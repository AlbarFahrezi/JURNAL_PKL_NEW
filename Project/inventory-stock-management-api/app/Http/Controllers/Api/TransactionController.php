<?php

namespace App\Http\Controllers\Api;

use App\Events\TransactionCompleted;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Transaction",
    description: "Transaction Management"
)]
class TransactionController extends Controller
{
    /**
     * Get all transactions.
     */
    #[OA\Get(
        path: "/api/transactions",
        summary: "Get All Transactions",
        description: "Get transactions with pagination, search, filtering, and sorting",
        security: [["sanctum" => []]],
        tags: ["Transaction"],
        parameters: [
            new OA\Parameter(
                name: "search",
                in: "query",
                required: false,
                description: "Search by transaction code",
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "status",
                in: "query",
                required: false,
                description: "Filter by transaction status",
                schema: new OA\Schema(
                    type: "string",
                    enum: ["draft", "completed", "cancelled"]
                )
            ),
            new OA\Parameter(
                name: "type",
                in: "query",
                required: false,
                description: "Filter by transaction type",
                schema: new OA\Schema(
                    type: "string",
                    enum: ["IN", "OUT"]
                )
            ),
            new OA\Parameter(
                name: "per_page",
                in: "query",
                required: false,
                description: "Number of records per page",
                schema: new OA\Schema(
                    type: "integer",
                    default: 10
                )
            ),
            new OA\Parameter(
                name: "sort_by",
                in: "query",
                required: false,
                description: "Column used for sorting",
                schema: new OA\Schema(
                    type: "string",
                    enum: [
                        "id",
                        "transaction_code",
                        "type",
                        "status",
                        "total",
                        "created_at"
                    ],
                    default: "created_at"
                )
            ),
            new OA\Parameter(
                name: "sort_order",
                in: "query",
                required: false,
                description: "Sort direction",
                schema: new OA\Schema(
                    type: "string",
                    enum: ["asc", "desc"],
                    default: "desc"
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Transactions retrieved successfully"
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated"
            )
        ]
    )]
    public function index(Request $request)
    {
        $allowedSortColumns = [
            'id',
            'transaction_code',
            'type',
            'status',
            'total',
            'created_at',
        ];

        $sortBy = $request->get('sort_by', 'created_at');

        if (!in_array($sortBy, $allowedSortColumns, true)) {
            $sortBy = 'created_at';
        }

        $sortOrder = strtolower(
            $request->get('sort_order', 'desc')
        );

        if (!in_array($sortOrder, ['asc', 'desc'], true)) {
            $sortOrder = 'desc';
        }

        $perPage = min(
            max((int) $request->get('per_page', 10), 1),
            100
        );

        $query = Transaction::with([
            'user',
            'details.product',
        ]);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(
                'transaction_code',
                'like',
                "%{$search}%"
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        if ($request->filled('type')) {
            $query->where(
                'type',
                $request->type
            );
        }

        $transactions = $query
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Transactions retrieved successfully',
            'data' => $transactions->items(),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
                'last_page' => $transactions->lastPage(),
            ],
        ]);
    }

    /**
     * Create a transaction as draft.
     */
    #[OA\Post(
        path: "/api/transactions",
        summary: "Create Transaction Draft",
        description: "Create a new transaction and save it as draft",
        security: [["sanctum" => []]],
        tags: ["Transaction"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["type", "details"],
                properties: [
                    new OA\Property(
                        property: "type",
                        type: "string",
                        enum: ["IN", "OUT"],
                        example: "OUT"
                    ),
                    new OA\Property(
                        property: "note",
                        type: "string",
                        nullable: true,
                        example: "Pengeluaran barang"
                    ),
                    new OA\Property(
                        property: "details",
                        type: "array",
                        minItems: 1,
                        items: new OA\Items(
                            properties: [
                                new OA\Property(
                                    property: "product_id",
                                    type: "integer",
                                    example: 2
                                ),
                                new OA\Property(
                                    property: "quantity",
                                    type: "integer",
                                    example: 2
                                ),
                            ]
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Transaction draft created successfully"
            ),
            new OA\Response(
                response: 422,
                description: "Validation error"
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated"
            )
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => [
                'required',
                Rule::in(['IN', 'OUT']),
            ],

            'note' => [
                'nullable',
                'string',
            ],

            'details' => [
                'required',
                'array',
                'min:1',
            ],

            'details.*.product_id' => [
                'required',
                'integer',
                'distinct',
                'exists:products,id',
            ],

            'details.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $transaction = DB::transaction(function () use (
            $validated,
            $request
        ) {
            $transaction = Transaction::create([
                'transaction_code' => $this->generateTransactionCode(),
                'user_id' => $request->user()->id,
                'type' => $validated['type'],
                'status' => 'draft',
                'total' => 0,
                'note' => $validated['note'] ?? null,
            ]);

            $total = 0;

            foreach ($validated['details'] as $detail) {
                $product = Product::findOrFail(
                    $detail['product_id']
                );

                $price = $product->price;

                $subtotal = $price * $detail['quantity'];

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'quantity' => $detail['quantity'],
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $transaction->update([
                'total' => $total,
            ]);

            TransactionLog::create([
                'transaction_id' => $transaction->id,
                'user_id' => $request->user()->id,
                'old_status' => null,
                'new_status' => 'draft',
                'note' => 'Transaksi dibuat sebagai draft.',
            ]);

            return $transaction;
        });

        return response()->json([
            'success' => true,
            'message' => 'Transaction draft created successfully',
            'data' => $transaction->load([
                'user',
                'details.product',
                'logs.user',
            ]),
        ], 201);
    }

    /**
     * Show transaction detail.
     */
    #[OA\Get(
        path: "/api/transactions/{transaction}",
        summary: "Get Transaction Detail",
        description: "Get transaction detail with products and audit logs",
        security: [["sanctum" => []]],
        tags: ["Transaction"],
        parameters: [
            new OA\Parameter(
                name: "transaction",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Transaction retrieved successfully"
            ),
            new OA\Response(
                response: 404,
                description: "Transaction not found"
            )
        ]
    )]
    public function show(Transaction $transaction)
    {
        return response()->json([
            'success' => true,
            'message' => 'Transaction retrieved successfully',
            'data' => $transaction->load([
                'user',
                'details.product',
                'logs.user',
            ]),
        ]);
    }

    /**
     * Update a draft transaction.
     */
    #[OA\Put(
        path: "/api/transactions/{transaction}",
        summary: "Update Transaction Draft",
        description: "Update a draft transaction. Stock is not changed until the transaction is completed.",
        security: [["sanctum" => []]],
        tags: ["Transaction"],
        parameters: [
            new OA\Parameter(
                name: "transaction",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["type", "details"],
                properties: [
                    new OA\Property(
                        property: "type",
                        type: "string",
                        enum: ["IN", "OUT"],
                        example: "OUT"
                    ),
                    new OA\Property(
                        property: "note",
                        type: "string",
                        nullable: true,
                        example: "Draft transaksi diperbarui"
                    ),
                    new OA\Property(
                        property: "details",
                        type: "array",
                        minItems: 1,
                        items: new OA\Items(
                            properties: [
                                new OA\Property(
                                    property: "product_id",
                                    type: "integer",
                                    example: 2
                                ),
                                new OA\Property(
                                    property: "quantity",
                                    type: "integer",
                                    example: 3
                                ),
                            ]
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Transaction updated successfully"
            ),
            new OA\Response(
                response: 422,
                description: "Only draft transactions can be updated or validation error"
            )
        ]
    )]
    public function update(
        Request $request,
        Transaction $transaction
    ) {
        $validated = $request->validate([
            'type' => [
                'required',
                Rule::in(['IN', 'OUT']),
            ],

            'note' => [
                'nullable',
                'string',
            ],

            'details' => [
                'required',
                'array',
                'min:1',
            ],

            'details.*.product_id' => [
                'required',
                'integer',
                'distinct',
                'exists:products,id',
            ],

            'details.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        try {
            $transaction = DB::transaction(function () use (
                $validated,
                $request,
                $transaction
            ) {
                if ($transaction->status !== 'draft') {
                    throw new \RuntimeException(
                        'Only draft transactions can be updated.'
                    );
                }

                $oldType = $transaction->type;

                $transaction->update([
                    'type' => $validated['type'],
                    'note' => $validated['note'] ?? null,
                ]);

                $transaction->details()->delete();

                $total = 0;

                foreach ($validated['details'] as $detail) {
                    $product = Product::findOrFail(
                        $detail['product_id']
                    );

                    $price = $product->price;

                    $subtotal = $price * $detail['quantity'];

                    TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $product->id,
                        'quantity' => $detail['quantity'],
                        'price' => $price,
                        'subtotal' => $subtotal,
                    ]);

                    $total += $subtotal;
                }

                $transaction->update([
                    'total' => $total,
                ]);

                TransactionLog::create([
                    'transaction_id' => $transaction->id,
                    'user_id' => $request->user()->id,
                    'old_status' => 'draft',
                    'new_status' => 'draft',
                    'note' =>
                        'Draft transaction updated.'
                        . ($oldType !== $validated['type']
                            ? ' Transaction type changed.'
                            : ''),
                ]);

                return $transaction->fresh([
                    'user',
                    'details.product',
                    'logs.user',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Transaction updated successfully.',
                'data' => $transaction,
            ], 200);

        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update transaction.',
            ], 500);
        }
    }

    /**
     * Delete a draft transaction.
     */
    #[OA\Delete(
        path: "/api/transactions/{transaction}",
        summary: "Delete Transaction Draft",
        description: "Delete a draft transaction",
        security: [["sanctum" => []]],
        tags: ["Transaction"],
        parameters: [
            new OA\Parameter(
                name: "transaction",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Transaction deleted successfully"
            ),
            new OA\Response(
                response: 422,
                description: "Only draft transactions can be deleted"
            )
        ]
    )]
    public function destroy(Transaction $transaction)
    {
        try {
            DB::transaction(function () use ($transaction) {
                if ($transaction->status !== 'draft') {
                    throw new \RuntimeException(
                        'Only draft transactions can be deleted.'
                    );
                }

                $transaction->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Transaction deleted successfully.',
            ], 200);

        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete transaction.',
            ], 500);
        }
    }

    /**
     * Complete transaction.
     *
     * Stock update, stock history, transaction status,
     * and audit trail are handled by TransactionCompleted event.
     */
    #[OA\Post(
        path: "/api/transactions/{transaction}/complete",
        summary: "Complete Transaction",
        description: "Complete a draft transaction and update product stock using an event listener atomically",
        security: [["sanctum" => []]],
        tags: ["Transaction"],
        parameters: [
            new OA\Parameter(
                name: "transaction",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Transaction completed successfully"
            ),
            new OA\Response(
                response: 422,
                description: "Transaction cannot be completed"
            ),
            new OA\Response(
                response: 401,
                description: "Unauthenticated"
            )
        ]
    )]
    public function complete(
        Request $request,
        Transaction $transaction
    ) {
        try {
            $result = DB::transaction(function () use (
                $transaction,
                $request
            ) {
                if ($transaction->status !== 'draft') {
                    throw new \RuntimeException(
                        'Only draft transactions can be completed.'
                    );
                }

                $transaction->load('details');

                if ($transaction->details->isEmpty()) {
                    throw new \RuntimeException(
                        'Transaction must have at least one detail.'
                    );
                }

                /*
                 * Dispatch event synchronously.
                 *
                 * HandleTransactionCompleted is automatically
                 * discovered by Laravel and performs:
                 * - master data validation
                 * - stock validation
                 * - stock update
                 * - stock history
                 * - transaction status update
                 * - audit trail
                 *
                 * Because this dispatch occurs inside DB::transaction(),
                 * an exception from the listener causes the entire
                 * transaction to rollback.
                 */
                TransactionCompleted::dispatch(
                    $transaction,
                    $request->user()->id
                );

                return $transaction->fresh([
                    'user',
                    'details.product',
                    'logs.user',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' =>
                    'Transaction completed successfully.',
                'data' => $result,
            ], 200);

        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete transaction.',
            ], 500);
        }
    }

    /**
     * Cancel a draft transaction.
     */
    #[OA\Post(
        path: "/api/transactions/{transaction}/cancel",
        summary: "Cancel Transaction Draft",
        description: "Cancel a draft transaction without changing product stock",
        security: [["sanctum" => []]],
        tags: ["Transaction"],
        parameters: [
            new OA\Parameter(
                name: "transaction",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Transaction cancelled successfully"
            ),
            new OA\Response(
                response: 422,
                description: "Only draft transactions can be cancelled"
            )
        ]
    )]
    public function cancel(
        Request $request,
        Transaction $transaction
    ) {
        try {
            $transaction = DB::transaction(function () use (
                $request,
                $transaction
            ) {
                if ($transaction->status !== 'draft') {
                    throw new \RuntimeException(
                        'Only draft transactions can be cancelled.'
                    );
                }

                $oldStatus = $transaction->status;

                $transaction->update([
                    'status' => 'cancelled',
                ]);

                TransactionLog::create([
                    'transaction_id' => $transaction->id,
                    'user_id' => $request->user()->id,
                    'old_status' => $oldStatus,
                    'new_status' => 'cancelled',
                    'note' => 'Transaction cancelled.',
                ]);

                return $transaction->fresh([
                    'user',
                    'details.product',
                    'logs.user',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' =>
                    'Transaction cancelled successfully.',
                'data' => $transaction,
            ], 200);

        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel transaction.',
            ], 500);
        }
    }

    /**
     * Generate unique transaction code.
     */
    private function generateTransactionCode(): string
    {
        do {
            $code =
                'TRX-'
                . now()->format('YmdHis')
                . '-'
                . strtoupper(Str::random(4));

        } while (
            Transaction::where(
                'transaction_code',
                $code
            )->exists()
        );

        return $code;
    }
}