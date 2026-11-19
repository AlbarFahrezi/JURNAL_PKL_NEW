import { useCallback, useEffect, useMemo, useState } from 'react';
import api from './services/api';
import { login, register } from './services/auth';
import './App.css';

import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Tooltip,
  Legend,
  Filler,
} from 'chart.js';
window.ChartJS = ChartJS;

import { Line, Doughnut } from 'react-chartjs-2';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Tooltip,
  Legend,
  Filler
);

const emptyPagination = {
  current_page: 1,
  per_page: 10,
  total: 0,
  last_page: 1,
  from: 0,
  to: 0,
};

function messageFromError(error) {
  const data = error?.response?.data;

  if (data?.errors) {
    return Object.values(data.errors).flat().join(' ');
  }

  return data?.message || error?.message || 'Terjadi kesalahan.';
}

function showToast(message, type = 'success') {
  window.dispatchEvent(
    new CustomEvent('app-toast', {
      detail: {
        message,
        type,
      },
    })
  );
}

/*
|--------------------------------------------------------------------------
| Helper untuk membaca response Laravel
|--------------------------------------------------------------------------
*/

function unwrap(response) {
  const payload = response?.data?.data ?? response?.data ?? null;

  if (Array.isArray(payload)) {
    return payload;
  }

  if (Array.isArray(payload?.data)) {
    return payload.data;
  }

  return payload;
}

function formatRupiah(value) {
  return `Rp ${Number(value || 0).toLocaleString('id-ID')}`;
}

function formatDate(value) {
  if (!value) return '-';

  return new Date(value).toLocaleString('id-ID');
}

function getLocalDateString() {
  const date = new Date();
  const offset = date.getTimezoneOffset();

  const localDate = new Date(
    date.getTime() - offset * 60000
  );

  return localDate.toISOString().slice(0, 10);
}

function Pagination({ pagination, onPage }) {
  if (!pagination || pagination.last_page <= 1) {
    return null;
  }

  return (
    <div className="pagination">
      <span>
        Menampilkan {pagination.from || 0}-{pagination.to || 0} dari{' '}
        {pagination.total} data
      </span>

      <div className="pagination-buttons">
        <button
          disabled={pagination.current_page <= 1}
          onClick={() => onPage(pagination.current_page - 1)}
        >
          ‹
        </button>

        <span>
          Halaman {pagination.current_page} / {pagination.last_page}
        </span>

        <button
          disabled={pagination.current_page >= pagination.last_page}
          onClick={() => onPage(pagination.current_page + 1)}
        >
          ›
        </button>
      </div>
    </div>
  );
}

function Modal({ title, onClose, children }) {
  return (
    <div className="modal-backdrop" onMouseDown={onClose}>
      <div
        className="modal"
        onMouseDown={(e) => e.stopPropagation()}
      >
        <div className="modal-header">
          <h3>{title}</h3>

          <button
            className="icon-button"
            onClick={onClose}
          >
            ×
          </button>
        </div>

        {children}
      </div>
    </div>
  );
}

function Dashboard({ user }) {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [period, setPeriod] = useState('7days');

  const load = useCallback(async (showLoading = false) => {
    if (showLoading) {
      setLoading(true);
    }

    setError('');

    try {
      const response = await api.get('/dashboard', {
        params: { period },
      });

      setData(unwrap(response));
    } catch (e) {
      setError(messageFromError(e));
    } finally {
      setLoading(false);
    }
  }, [period]);

  useEffect(() => {
    load(true);

    const interval = setInterval(() => {
      load(false);
    }, 60000);

    return () => clearInterval(interval);
  }, [load]);

  const numberFormat = (value) =>
    Number(value ?? 0).toLocaleString('id-ID');

const stockTrend = Array.isArray(data?.stock_trend)
  ? data.stock_trend
  : [];

const rawCategories = data?.category_composition;

const categories = Array.isArray(rawCategories)
  ? rawCategories
  : Array.isArray(rawCategories?.data)
    ? rawCategories.data
    : Array.isArray(rawCategories?.categories)
      ? rawCategories.categories
      : [];

console.log('RAW CATEGORY:', rawCategories);
console.log('FINAL CATEGORY:', categories);
console.log('FINAL CATEGORY LENGTH:', categories.length);      

const rawTransactions = data?.transaction_composition;

const transactions = Array.isArray(rawTransactions)
  ? rawTransactions
  : Array.isArray(rawTransactions?.data)
    ? rawTransactions.data
    : [];

console.log('CATEGORY COMPOSITION:', data?.category_composition);
console.log('CATEGORY IS ARRAY:', Array.isArray(data?.category_composition));
console.log('CATEGORY TYPE:', typeof data?.category_composition);

  const stockChartData = {
    labels: stockTrend.map((item) => item.date),
    datasets: [
      {
        label: 'Stock In',
        data: stockTrend.map((item) => item.stock_in),
        borderColor: '#16a34a',
        backgroundColor: 'rgba(22, 163, 74, 0.12)',
        tension: 0.35,
        fill: true,
      },
      {
        label: 'Stock Out',
        data: stockTrend.map((item) => item.stock_out),
        borderColor: '#dc2626',
        backgroundColor: 'rgba(220, 38, 38, 0.08)',
        tension: 0.35,
        fill: true,
      },
      {
        label: 'Adjustment',
        data: stockTrend.map((item) => item.adjustment),
        borderColor: '#2563eb',
        backgroundColor: 'rgba(37, 99, 235, 0.08)',
        tension: 0.35,
        fill: false,
      },
    ],
  };

  const stockChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
      intersect: false,
      mode: 'index',
    },
    plugins: {
      legend: {
        position: 'bottom',
      },
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          precision: 0,
        },
      },
    },
  };

  const categoryChartData = {
    labels: categories.map((item) => item.name),
    datasets: [
      {
        label: 'Jumlah Produk',
        data: categories.map((item) =>
          Number(item.total_products)
        ),
        backgroundColor: [
          '#2563eb',
          '#16a34a',
          '#f59e0b',
          '#dc2626',
          '#8b5cf6',
          '#06b6d4',
          '#ec4899',
          '#64748b',
          '#84cc16',
          '#f97316',
          '#14b8a6',
        ],
        borderWidth: 2,
        borderColor: '#ffffff',
      },
    ],
  };

    console.log('CATEGORY CHART LABELS:', categoryChartData.labels);
  console.log(
    'CATEGORY CHART VALUES:',
    categoryChartData.datasets[0].data
  );


  const transactionLabels = {
    draft: 'Draft',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
  };

  const transactionChartData = {
    labels: transactions.map(
      (item) => transactionLabels[item.status] || item.status
    ),
    datasets: [
      {
        label: 'Jumlah Transaksi',
        data: transactions.map((item) => Number(item.total)),
        backgroundColor: [
          '#f59e0b',
          '#16a34a',
          '#dc2626',
        ],
        borderWidth: 2,
        borderColor: '#ffffff',
      },
    ],
  };

  const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'bottom',
      },
    },
  };

  if (loading && !data) {
    return (
      <PageTitle
        title="Dashboard"
        subtitle="Memuat ringkasan inventory..."
        loading
      />
    );
  }

  return (
    <>
      <PageTitle
        title="Dashboard"
        subtitle={`Selamat datang, ${user?.name || 'User'} (${user?.role || '-'})`}
        action={
          <button onClick={() => load(false)} disabled={loading}>
            {loading ? 'Memuat...' : 'Refresh'}
          </button>
        }
      />

      {error && <Alert>{error}</Alert>}

      {data && (
        <>
          <div className="dashboard-toolbar">
            <div>
              <strong>Statistik Inventory</strong>
              <span>Data diperbarui otomatis setiap 60 detik</span>
            </div>

            <select
              value={period}
              onChange={(e) => setPeriod(e.target.value)}
            >
              <option value="7days">7 Hari Terakhir</option>
              <option value="30days">30 Hari Terakhir</option>
              <option value="year">Tahun Ini</option>
            </select>
          </div>

          <div className="stats-grid">
            <Stat title="Total Produk" value={data.total_products} />
            <Stat title="Total Stok" value={data.total_stock} />
            <Stat title="Stok Rendah" value={data.low_stock_count} />
            <Stat title="Stok Habis" value={data.out_of_stock_count} />
            <Stat title="Stock In Hari Ini" value={data.stock_in_today} />
            <Stat title="Stock Out Hari Ini" value={data.stock_out_today} />

            {user?.role === 'administrator' && (
              <>
                <Stat title="Total Kategori" value={data.total_categories} />
                <Stat title="Total Supplier" value={data.total_suppliers} />
                <Stat title="Total Warehouse" value={data.total_warehouses} />
                <Stat
                  title="Adjustment Hari Ini"
                  value={data.stock_adjustment_today}
                />
              </>
            )}
          </div>

          <section className="dashboard-chart-card">
            <div className="dashboard-chart-header">
              <div>
                <h3>Pergerakan Stok</h3>
                <p>Grafik Stock In, Stock Out, dan Adjustment</p>
              </div>
            </div>

            <div className="dashboard-line-chart">
              <Line
                data={stockChartData}
                options={stockChartOptions}
              />
            </div>
          </section>

          <div className="dashboard-chart-grid">
            <section className="dashboard-chart-card">
              <div className="dashboard-chart-header">
                <div>
                  <h3>Komposisi Kategori</h3>
                  <p>Jumlah produk pada setiap kategori</p>
                </div>
              </div>

              <div className="dashboard-doughnut-chart">
                <Doughnut
                  data={categoryChartData}
                  options={doughnutOptions}
                />
              </div>
            </section>

            <section className="dashboard-chart-card">
              <div className="dashboard-chart-header">
                <div>
                  <h3>Status Transaksi</h3>
                  <p>Seluruh transaksi berdasarkan status</p>
                </div>
              </div>

              <div className="dashboard-doughnut-chart">
                <Doughnut
                  data={transactionChartData}
                  options={doughnutOptions}
                />
              </div>
            </section>
          </div>

          <section className="dashboard-chart-card">
            <div className="dashboard-chart-header">
              <div>
                <h3>Daftar Stok Rendah</h3>
                <p>
                  Produk dengan stok antara 1 sampai{' '}
                  {data.low_stock_threshold}
                </p>
              </div>
            </div>

            {data.low_stock_products?.length > 0 ? (
              <div className="table-wrapper">
                <table>
                  <thead>
                    <tr>
                      <th>Nama Produk</th>
                      <th>SKU</th>
                      <th>Sisa Stok</th>
                    </tr>
                  </thead>
                  <tbody>
                    {data.low_stock_products.map((product) => (
                      <tr key={product.id}>
                        <td>{product.name}</td>
                        <td>{product.sku}</td>
                        <td>
                          <span className="stock-low">
                            {numberFormat(product.stock)}
                          </span>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            ) : (
              <p className="dashboard-empty">
                Tidak ada produk dengan stok rendah.
              </p>
            )}
          </section>

          <div className="dashboard-last-updated">
            Terakhir diperbarui: {data.last_updated || '-'}
          </div>
        </>
      )}
    </>
  );
}

function Stat({ title, value }) {
  const displayValue =
    typeof value === 'string'
      ? value
      : Number(value ?? 0).toLocaleString('id-ID');

  return (
    <div className="stat-card">
      <span>{title}</span>

      <strong>{displayValue}</strong>
    </div>
  );
}

function PageTitle({
  title,
  subtitle,
  action,
  loading,
}) {
  return (
    <div className="page-title">
      <div>
        <h2>{title}</h2>
        <p>{subtitle}</p>
      </div>

      {action}
    </div>
  );
}

function Alert({
  children,
  type = 'error',
}) {
  return (
    <div className={`alert ${type}`}>
      {children}
    </div>
  );
}

/*
|--------------------------------------------------------------------------
| MASTER PAGE
|--------------------------------------------------------------------------
*/

function MasterPage({ type, canManage }) {
  const configs = {
    categories: {
      title: 'Categories',
      singular: 'Category',
      fields: [
        {
          key: 'name',
          label: 'Nama',
          required: true,
        },
        {
          key: 'description',
          label: 'Deskripsi',
          textarea: true,
        },
      ],
    },

    suppliers: {
      title: 'Suppliers',
      singular: 'Supplier',
      fields: [
        {
          key: 'name',
          label: 'Nama',
          required: true,
        },
        {
          key: 'phone',
          label: 'Telepon',
        },
        {
          key: 'address',
          label: 'Alamat',
          textarea: true,
        },
      ],
    },

    warehouses: {
      title: 'Warehouses',
      singular: 'Warehouse',
      fields: [
        {
          key: 'name',
          label: 'Nama',
          required: true,
        },
        {
          key: 'location',
          label: 'Lokasi',
        },
        {
          key: 'description',
          label: 'Deskripsi',
          textarea: true,
        },
      ],
    },
  };

  const cfg = configs[type];

  const [items, setItems] = useState([]);
  const [pagination, setPagination] =
    useState(emptyPagination);

  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(10);

  const [sortBy, setSortBy] =
    useState('created_at');

  const [sortOrder, setSortOrder] =
    useState('desc');

  const [modal, setModal] = useState(null);
  const [form, setForm] = useState({});

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const load = async (targetPage = page) => {
    setLoading(true);
    setError('');

    try {
      const r = await api.get(`/${type}`, {
        params: {
          search: search || undefined,
          page: targetPage,
          per_page: perPage,
          sort_by: sortBy,
          sort_order: sortOrder,
        },
      });

      setItems(unwrap(r) || []);

      setPagination(
        r.data.pagination || emptyPagination
      );
    } catch (e) {
      setError(messageFromError(e));
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    load(page);
  }, [
    page,
    perPage,
    sortBy,
    sortOrder,
  ]);

  useEffect(() => {
    const t = setTimeout(() => {
      setPage(1);
      load(1);
    }, 400);

    return () => clearTimeout(t);
  }, [search]);

  const submit = async (e) => {
    e.preventDefault();

    setLoading(true);
    setError('');

    try {
      if (modal === 'create') {
        await api.post(`/${type}`, form);
      } else {
        await api.put(
          `/${type}/${form.id}`,
          form
        );
      }

      showToast(
        modal === 'create'
          ? `${cfg.singular} berhasil ditambahkan!`
          : `${cfg.singular} berhasil diperbarui!`,
        'success'
      );
      setModal(null);
      setForm({});

      await load(page);
    } catch (e) {
      const errorMessage = messageFromError(e);
      setError(errorMessage);
      showToast(errorMessage, 'error');
    } finally {
      setLoading(false);
    }
  };

  const remove = async (item) => {
    if (!confirm(`Hapus ${item.name}?`)) {
      return;
    }

    setError('');

    try {
      await api.delete(
        `/${type}/${item.id}`
      );

      showToast(
        `${cfg.singular} berhasil dihapus!`,
        'success'
      );

      await load(page);
    } catch (e) {
      const errorMessage = messageFromError(e);
      setError(errorMessage);
      showToast(errorMessage, 'error');
    }
  };

  return (
    <>
      <PageTitle
        title={cfg.title}
        subtitle={`Kelola data master ${cfg.singular.toLowerCase()}.`}
        action={
          canManage && (
            <button
              className="primary"
              onClick={() => {
                setForm({});
                setModal('create');
              }}
            >
              + Tambah
            </button>
          )
        }
      />

      {error && <Alert>{error}</Alert>}

      <section className="card">
        <div className="toolbar">
          <input
            className="search"
            placeholder={`Cari ${cfg.singular.toLowerCase()}...`}
            value={search}
            onChange={(e) =>
              setSearch(e.target.value)
            }
          />

          <select
            value={sortBy}
            onChange={(e) => {
              setSortBy(e.target.value);
              setPage(1);
            }}
          >
            <option value="created_at">
              Terbaru
            </option>

            <option value="name">
              Nama
            </option>
          </select>

          <select
            value={sortOrder}
            onChange={(e) => {
              setSortOrder(e.target.value);
              setPage(1);
            }}
          >
            <option value="desc">
              Desc
            </option>

            <option value="asc">
              Asc
            </option>
          </select>

          <select
            value={perPage}
            onChange={(e) => {
              setPerPage(
                Number(e.target.value)
              );

              setPage(1);
            }}
          >
            <option>10</option>
            <option>25</option>
            <option>50</option>
            <option>100</option>
          </select>

          <button
            onClick={() => {
              setSearch('');
              setPage(1);
              setSortBy('created_at');
              setSortOrder('desc');
            }}
          >
            Reset
          </button>
        </div>

        {loading ? (
          <p>Memuat data...</p>
        ) : (
          <div className="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>ID</th>

                  {cfg.fields.map((f) => (
                    <th key={f.key}>
                      {f.label}
                    </th>
                  ))}

                  {canManage && (
                    <th>Aksi</th>
                  )}
                </tr>
              </thead>

              <tbody>
                {items.length === 0 ? (
                  <tr>
                    <td
                      colSpan={
                        cfg.fields.length + 2
                      }
                    >
                      Tidak ada data.
                    </td>
                  </tr>
                ) : (
                  items.map((item) => (
                    <tr key={item.id}>
                      <td>{item.id}</td>

                      {cfg.fields.map((f) => (
                        <td key={f.key}>
                          {item[f.key] || '-'}
                        </td>
                      ))}

                      {canManage && (
                        <td className="actions">
                          <button
                            onClick={() => {
                              setForm({
                                ...item,
                              });

                              setModal('edit');
                            }}
                          >
                            Edit
                          </button>

                          <button
                            className="danger-text"
                            onClick={() =>
                              remove(item)
                            }
                          >
                            Hapus
                          </button>
                        </td>
                      )}
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        )}

        <Pagination
          pagination={pagination}
          onPage={(p) => setPage(p)}
        />
      </section>

      {modal && (
        <Modal
          title={`${modal === 'create' ? 'Tambah' : 'Edit'} ${cfg.singular}`}
          onClose={() =>
            setModal(null)
          }
        >
          <form onSubmit={submit}>
            {cfg.fields.map((f) => (
              <Field
                key={f.key}
                label={f.label}
                value={
                  form[f.key] || ''
                }
                onChange={(v) =>
                  setForm({
                    ...form,
                    [f.key]: v,
                  })
                }
                required={f.required}
                textarea={f.textarea}
              />
            ))}

            <div className="form-actions">
              <button
                type="button"
                onClick={() =>
                  setModal(null)
                }
              >
                Batal
              </button>

              <button
                className="primary"
                disabled={loading}
              >
                {loading
                  ? 'Menyimpan...'
                  : 'Simpan'}
              </button>
            </div>
          </form>
        </Modal>
      )}
    </>
  );
}

function Field({
  label,
  value,
  onChange,
  type = 'text',
  required = false,
  textarea = false,
}) {
  return (
    <label className="field">
      <span>
        {label}
        {required && ' *'}
      </span>

      {textarea ? (
        <textarea
          value={value}
          onChange={(e) =>
            onChange(e.target.value)
          }
          required={required}
          rows="3"
        />
      ) : (
        <input
          type={type}
          value={value}
          onChange={(e) =>
            onChange(e.target.value)
          }
          required={required}
        />
      )}
    </label>
  );
}

/*
|--------------------------------------------------------------------------
| PRODUCT PAGE
|--------------------------------------------------------------------------
*/

function ProductPage({ canManage }) {
  const [products, setProducts] =
    useState([]);

  const [categories, setCategories] =
    useState([]);

  const [suppliers, setSuppliers] =
    useState([]);

  const [warehouses, setWarehouses] =
    useState([]);

  const [pagination, setPagination] =
    useState(emptyPagination);

  const [filters, setFilters] =
    useState({
      search: '',
      category_id: '',
      date_from: '',
      date_to: '',
      per_page: 10,
      sort_by: 'created_at',
      sort_order: 'desc',
    });

  const [page, setPage] =
    useState(1);

  const [modal, setModal] =
    useState(null);

  const [form, setForm] =
    useState({});

  const [loading, setLoading] =
    useState(false);

  const [error, setError] =
    useState('');

  const [exportLoading, setExportLoading] =
  useState('');  

  const [success, setSuccess] =
    useState('');

  const loadOptions = async () => {
    try {
      const [c, s, w] =
        await Promise.all([
          api.get('/categories', {
            params: {
              per_page: 100,
              sort_by: 'name',
              sort_order: 'asc',
            },
          }),

          api.get('/suppliers', {
            params: {
              per_page: 100,
              sort_by: 'name',
              sort_order: 'asc',
            },
          }),

          api.get('/warehouses', {
            params: {
              per_page: 100,
              sort_by: 'name',
              sort_order: 'asc',
            },
          }),
        ]);

      setCategories(
        unwrap(c) || []
      );

      setSuppliers(
        unwrap(s) || []
      );

      setWarehouses(
        unwrap(w) || []
      );
    } catch (e) {
      setError(
        messageFromError(e)
      );
    }
  };

  const load = async (
    targetPage = page
  ) => {
    setLoading(true);
    setError('');

    try {
      const r = await api.get(
        '/products',
        {
          params: {
            ...filters,

            category_id:
              filters.category_id ||
              undefined,

            search:
              filters.search ||
              undefined,

            date_from:
              filters.date_from ||
              undefined,

            date_to:
              filters.date_to ||
              undefined,

            page: targetPage,
          },
        }
      );

      setProducts(
        unwrap(r) || []
      );

      setPagination(
        r.data.pagination ||
          emptyPagination
      );
    } catch (e) {
      setError(messageFromError(e));
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadOptions();
  }, []);

  useEffect(() => {
    load(page);
  }, [
    page,
    filters.category_id,
    filters.date_from,
    filters.date_to,
    filters.per_page,
    filters.sort_by,
    filters.sort_order,
  ]);

  useEffect(() => {
    if (filters.search === '') {
      return;
    }

    const timer = setTimeout(() => {
      setPage(1);
      load(1);
    }, 400);

    return () => clearTimeout(timer);
  }, [filters.search]);

  const updateFilter = (
    key,
    value
  ) => {
    setFilters((f) => ({
      ...f,
      [key]: value,
    }));

    setPage(1);
  };

  const reset = () => {
    setFilters({
      search: '',
      category_id: '',
      date_from: '',
      date_to: '',
      per_page: 10,
      sort_by: 'created_at',
      sort_order: 'desc',
    });

    setPage(1);
  };

  const submit = async (e) => {
    e.preventDefault();

    setLoading(true);
    setError('');
    setSuccess('');

    try {
      const payload = {
        category_id:
          Number(form.category_id),

        supplier_id:
          Number(form.supplier_id),

        warehouse_id:
          Number(form.warehouse_id),

        name: form.name,

        sku: form.sku,

        price:
          Number(form.price),

        description:
          form.description ||
          undefined,
      };

      if (modal === 'edit') {
        payload.lock_version =
          Number(form.lock_version);

        await api.put(
          `/products/${form.id}`,
          payload
        );
      } else {
        await api.post(
          '/products',
          payload
        );
      }

      setModal(null);
      setForm({});

      

      showToast(
  modal === 'create'
    ? 'Product berhasil ditambahkan!'
    : 'Product berhasil diperbarui!',
  'success'
);


      await load(page);
    } catch (e) {
      setError(
        messageFromError(e)
      );
        showToast(
    messageFromError(e),
    'error'

        );
    } finally {
      setLoading(false);
    }
  };

  const remove = async (p) => {
    if (
      !confirm(
        `Soft delete product "${p.name}"?`
      )
    ) {
      return;
    }

    try {
      setError('');

      await api.delete(
        `/products/${p.id}`
      );

      setSuccess(
        'Product berhasil dihapus (soft delete).'
      );

      showToast(
  'Product berhasil dihapus!',
  'success'
);

      await load(page);
    } catch (e) {
      setError(
        messageFromError(e)
      );
    }
  };

  const startEdit = async (p) => {
    try {
      const r = await api.get(
        `/products/${p.id}`
      );

      setForm(
        unwrap(r)
      );

      setModal('edit');
    } catch (e) {
      setError(
        messageFromError(e)
      );
    }
  };

  return (
    <>
      <PageTitle
        title="Products"
        subtitle="Master product dengan search, filter, sorting, pagination, soft delete, dan optimistic locking."
        action={
          canManage && (
            <button
              className="primary"
              onClick={() => {
                setForm({
                  category_id: '',
                  supplier_id: '',
                  warehouse_id: '',
                  name: '',
                  sku: '',
                  price: '',
                  description: '',
                });

                setModal('create');
              }}
            >
              + Tambah Product
            </button>
          )
        }
      />

      {loading && (
  <div className="loading-message">
    Memuat data produk...
  </div>
)}

      {success && (
        <Alert type="success">
          {success}
        </Alert>
      )}

      {error && (
        <Alert>
          {error}
        </Alert>
      )}

      <section className="card">
        <div className="filter-grid">
          <input
            placeholder="Cari nama / SKU..."
            value={filters.search}
            onChange={(e) =>
              updateFilter(
                'search',
                e.target.value
              )
            }
          />

          <select
            value={filters.category_id}
            onChange={(e) =>
              updateFilter(
                'category_id',
                e.target.value
              )
            }
          >
            <option value="">
              Semua kategori
            </option>

            {categories.map((c) => (
              <option
                key={c.id}
                value={c.id}
              >
                {c.name}
              </option>
            ))}
          </select>

          <label>
            Dari{' '}
            <input
              type="date"
              value={
                filters.date_from
              }
              onChange={(e) =>
                updateFilter(
                  'date_from',
                  e.target.value
                )
              }
            />
          </label>

          <label>
            Sampai{' '}
            <input
              type="date"
              value={
                filters.date_to
              }
              onChange={(e) =>
                updateFilter(
                  'date_to',
                  e.target.value
                )
              }
            />
          </label>

          <select
            value={filters.sort_by}
            onChange={(e) =>
              updateFilter(
                'sort_by',
                e.target.value
              )
            }
          >
            <option value="created_at">
              Tanggal
            </option>

            <option value="name">
              Nama
            </option>

            <option value="sku">
              SKU
            </option>

            <option value="price">
              Harga
            </option>

            <option value="stock">
              Stok
            </option>

            <option value="id">
              ID
            </option>
          </select>

          <select
            value={filters.sort_order}
            onChange={(e) =>
              updateFilter(
                'sort_order',
                e.target.value
              )
            }
          >
            <option value="desc">
              Descending
            </option>

            <option value="asc">
              Ascending
            </option>
          </select>

          <select
            value={filters.per_page}
            onChange={(e) =>
              updateFilter(
                'per_page',
                Number(e.target.value)
              )
            }
          >
            <option value="10">
              10 / halaman
            </option>

            <option value="25">
              25 / halaman
            </option>

            <option value="50">
              50 / halaman
            </option>

            <option value="100">
              100 / halaman
            </option>
          </select>

          <button onClick={reset}>
            Reset Filter
          </button>
        </div>

        {loading ? (
          <p>
            Memuat product...
          </p>
        ) : (
          <div className="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Nama</th>
                  <th>SKU</th>
                  <th>Kategori</th>
                  <th>Harga</th>
                  <th>Stok</th>
                  <th>Warehouse</th>

                  {canManage && (
                    <th>Aksi</th>
                  )}
                </tr>
              </thead>

              <tbody>
                {products.length === 0 ? (
                  <tr>
                    <td colSpan="8">
                      Tidak ada data product.
                    </td>
                  </tr>
                ) : (
                  products.map((p) => (
                    <tr key={p.id}>
                      <td>{p.id}</td>

                      <td>
                        {p.name}
                      </td>

                      <td>
                        {p.sku}
                      </td>

                      <td>
                        {p.category?.name ||
                          '-'}
                      </td>

                      <td>
                        {formatRupiah(
                          p.price
                        )}
                      </td>

                      <td>
                        <span
                          className={
                            p.stock <= 5
                              ? 'stock-low'
                              : ''
                          }
                        >
                          {p.stock}
                        </span>
                      </td>

                      <td>
                        {p.warehouse?.name ||
                          '-'}
                      </td>

                      {canManage && (
                        <td className="actions">
                          <button
                            onClick={() =>
                              startEdit(p)
                            }
                          >
                            Edit
                          </button>

                          <button
                            className="danger-text"
                            onClick={() =>
                              remove(p)
                            }
                          >
                            Hapus
                          </button>
                        </td>
                      )}
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        )}

        <Pagination
          pagination={pagination}
          onPage={setPage}
        />
      </section>

      {modal && (
        <Modal
          title={`${modal === 'create' ? 'Tambah' : 'Edit'} Product`}
          onClose={() =>
            setModal(null)
          }
        >
          <form onSubmit={submit}>
            <Field
              label="Nama"
              value={
                form.name || ''
              }
              onChange={(v) =>
                setForm({
                  ...form,
                  name: v,
                })
              }
              required
            />

            <Field
              label="SKU"
              value={
                form.sku || ''
              }
              onChange={(v) =>
                setForm({
                  ...form,
                  sku: v,
                })
              }
              required
            />

            <Field
              label="Harga"
              type="number"
              value={
                form.price ?? ''
              }
              onChange={(v) =>
                setForm({
                  ...form,
                  price: v,
                })
              }
              required
            />

            <SelectField
              label="Category"
              value={
                form.category_id ||
                ''
              }
              onChange={(v) =>
                setForm({
                  ...form,
                  category_id: v,
                })
              }
              options={categories}
              required
            />

            <SelectField
              label="Supplier"
              value={
                form.supplier_id ||
                ''
              }
              onChange={(v) =>
                setForm({
                  ...form,
                  supplier_id: v,
                })
              }
              options={suppliers}
              required
            />

            <SelectField
              label="Warehouse"
              value={
                form.warehouse_id ||
                ''
              }
              onChange={(v) =>
                setForm({
                  ...form,
                  warehouse_id: v,
                })
              }
              options={warehouses}
              required
            />

            <Field
              label="Deskripsi"
              value={
                form.description ||
                ''
              }
              onChange={(v) =>
                setForm({
                  ...form,
                  description: v,
                })
              }
              textarea
            />

            {modal === 'edit' && (
              <p className="hint">
                Lock version saat ini:{' '}
                {form.lock_version}
              </p>
            )}

            <div className="form-actions">
              <button
                type="button"
                onClick={() =>
                  setModal(null)
                }
              >
                Batal
              </button>

              <button
                className="primary"
                disabled={loading}
              >
                {loading
                  ? 'Menyimpan...'
                  : 'Simpan'}
              </button>
            </div>
          </form>
        </Modal>
      )}
    </>
  );
}

function SelectField({
  label,
  value,
  onChange,
  options,
  required,
}) {
  return (
    <label className="field">
      <span>
        {label}
        {required && ' *'}
      </span>

      <select
        value={value}
        onChange={(e) =>
          onChange(e.target.value)
        }
        required={required}
      >
        <option value="">
          Pilih {label}
        </option>

        {options.map((o) => (
          <option
            key={o.id}
            value={o.id}
          >
            {o.name}
          </option>
        ))}
      </select>
    </label>
  );
}

/*
|--------------------------------------------------------------------------
| STOCK PAGE
|--------------------------------------------------------------------------
*/

function StockPage() {
  const [products, setProducts] =
    useState([]);

  const [mode, setMode] =
    useState('IN');

  const [form, setForm] =
    useState({
      product_id: '',
      quantity: 1,
      stock: 0,
      note: '',
    });

  const [error, setError] =
    useState('');

  const [loading, setLoading] =
    useState(false);

 useEffect(() => {
  const loadProducts = async () => {
    setLoading(true);
    setError('');

    try {
      const response = await api.get('/products', {
        params: {
          per_page: 100,
        },
      });

      setProducts(unwrap(response) || []);
    } catch (e) {
      setError(messageFromError(e));
    } finally {
      setLoading(false);
    }
  };

  loadProducts();
}, []);

  const submit = async (e) => {
    e.preventDefault();

    setLoading(true);
    setError('');

    try {
      const path =
        mode === 'IN'
          ? '/stock-in'
          : mode === 'OUT'
            ? '/stock-out'
            : '/stock-adjustment';

      const payload =
        mode === 'ADJUSTMENT'
          ? {
              product_id:
                Number(
                  form.product_id
                ),

              stock:
                Number(
                  form.stock
                ),

              note:
                form.note ||
                undefined,
            }
          : {
              product_id:
                Number(
                  form.product_id
                ),

              quantity:
                Number(
                  form.quantity
                ),

              note:
                form.note ||
                undefined,
            };

      const r = await api.post(
        path,
        payload
      );

      showToast(
        r.data.message ||
          'Stok berhasil diproses.'
      );

      setForm({
        product_id: '',
        quantity: 1,
        stock: 0,
        note: '',
      });
    } catch (e) {
      setError(
        messageFromError(e)
      );
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <PageTitle
        title="Stock Management"
        subtitle="Stock In, Stock Out, dan Stock Adjustment."
      />

      {error && (
        <Alert>
          {error}
        </Alert>
      )}

      <section className="card narrow">
        <div className="tabs">
          {[
            'IN',
            'OUT',
            'ADJUSTMENT',
          ].map((x) => (
            <button
              key={x}
              className={
                mode === x
                  ? 'active'
                  : ''
              }
              onClick={() =>
                setMode(x)
              }
            >
              {x === 'IN'
                ? 'Stock In'
                : x === 'OUT'
                  ? 'Stock Out'
                  : 'Adjustment'}
            </button>
          ))}
        </div>

        <form onSubmit={submit}>
          <SelectField
            label="Product"
            value={form.product_id}
            onChange={(v) =>
              setForm({
                ...form,
                product_id: v,
              })
            }
            options={products}
            required
          />

          {mode === 'ADJUSTMENT' ? (
            <Field
              label="Stok baru"
              type="number"
              value={form.stock}
              onChange={(v) =>
                setForm({
                  ...form,
                  stock: v,
                })
              }
              required
            />
          ) : (
            <Field
              label="Jumlah"
              type="number"
              value={form.quantity}
              onChange={(v) =>
                setForm({
                  ...form,
                  quantity: v,
                })
              }
              required
            />
          )}

          <Field
            label="Catatan"
            value={form.note}
            onChange={(v) =>
              setForm({
                ...form,
                note: v,
              })
            }
            textarea
          />

          <button
            className="primary"
            disabled={loading}
          >
            {loading
              ? 'Memproses...'
              : 'Simpan'}
          </button>
        </form>
      </section>
    </>
  );
}

/*
|--------------------------------------------------------------------------
| STOCK HISTORY
|--------------------------------------------------------------------------
*/

function StockHistoryPage() {
  const [items, setItems] =
    useState([]);

  const [pagination, setPagination] =
    useState(emptyPagination);

  const [search, setSearch] =
    useState('');

  const [type, setType] =
    useState('');

  const [page, setPage] =
    useState(1);

  const [perPage, setPerPage] =
    useState(10);

  const [error, setError] =
    useState('');

  const [loading, setLoading] =
    useState(false);

  const load = async (
    p = page
  ) => {
    setLoading(true);
    setError('');

    try {
      const r = await api.get(
        '/stock-history',
        {
          params: {
            search:
              search || undefined,

            type:
              type || undefined,

            page: p,

            per_page:
              perPage,
          },
        }
      );

      setItems(
        unwrap(r) || []
      );

      setPagination(
        r.data.pagination ||
          emptyPagination
      );
    } catch (e) {
      setError(
        messageFromError(e)
      );

      showToast(
    messageFromError(e),
    'error'
  );

    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    load(page);
  }, [
    page,
    type,
    perPage,
  ]);

  useEffect(() => {
    const t = setTimeout(() => {
      setPage(1);
      load(1);
    }, 400);

    return () =>
      clearTimeout(t);
  }, [search]);

  return (
    <>
      <PageTitle
        title="Stock History"
        subtitle="Riwayat perubahan stok dari stock management dan transaksi."
      />

      {error && (
        <Alert>
          {error}
        </Alert>
      )}

      <section className="card">
        <div className="toolbar">
          <input
            className="search"
            placeholder="Cari product / catatan..."
            value={search}
            onChange={(e) =>
              setSearch(e.target.value)
            }
          />

          <select
            value={type}
            onChange={(e) => {
              setType(
                e.target.value
              );

              setPage(1);
            }}
          >
            <option value="">
              Semua tipe
            </option>

            <option value="IN">
              IN
            </option>

            <option value="OUT">
              OUT
            </option>

            <option value="ADJUSTMENT">
              ADJUSTMENT
            </option>
          </select>

          <select
            value={perPage}
            onChange={(e) => {
              setPerPage(
                Number(
                  e.target.value
                )
              );

              setPage(1);
            }}
          >
            <option>10</option>
            <option>25</option>
            <option>50</option>
            <option>100</option>
          </select>

          <button
            onClick={() => {
              setSearch('');
              setType('');
              setPage(1);
            }}
          >
            Reset Filter
          </button>
        </div>

        {loading ? (
          <p>
            Memuat riwayat...
          </p>
        ) : (
          <div className="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Product</th>
                  <th>Tipe</th>
                  <th>Qty</th>
                  <th>Stok</th>
                  <th>Catatan</th>
                  <th>Tanggal</th>
                </tr>
              </thead>

              <tbody>
                {items.length === 0 ? (
                  <tr>
                    <td colSpan="7">
                      Tidak ada data.
                    </td>
                  </tr>
                ) : (
                  items.map((i) => (
                    <tr key={i.id}>
                      <td>{i.id}</td>

                      <td>
                        {i.product?.name ||
                          '-'}
                      </td>

                      <td>
                        <span
                          className={`badge ${i.type.toLowerCase()}`}
                        >
                          {i.type}
                        </span>
                      </td>

                      <td>
                        {i.quantity}
                      </td>

                      <td>
                        {i.stock_before} →{' '}
                        {i.stock_after}
                      </td>

                      <td>
                        {i.note || '-'}
                      </td>

                      <td>
                        {formatDate(
                          i.created_at
                        )}
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        )}

        <Pagination
          pagination={pagination}
          onPage={setPage}
        />
      </section>
    </>
  );
}

/*
|--------------------------------------------------------------------------
| TRANSACTIONS
|--------------------------------------------------------------------------
*/

function TransactionsPage() {
  const [items, setItems] =
    useState([]);

  const [pagination, setPagination] =
    useState(emptyPagination);

  const [products, setProducts] =
    useState([]);

  const [filters, setFilters] =
    useState({
      search: '',
      status: '',
      type: '',
      per_page: 10,
    });

  const [page, setPage] =
    useState(1);

  const [modal, setModal] =
    useState(null);

  const [detail, setDetail] =
    useState(null);

  const [form, setForm] =
    useState({
      type: 'IN',
      note: '',
      details: [
        {
          product_id: '',
          quantity: 1,
        },
      ],
    });

  const [error, setError] =
    useState('');

  const [success, setSuccess] =
    useState('');

  const [loading, setLoading] =
    useState(false);

  const loadProducts =
    async () => {
      try {
        const r =
          await api.get(
            '/products',
            {
              params: {
                per_page: 100,
              },
            }
          );

        setProducts(
          unwrap(r) || []
        );
      } catch (e) {
        setError(
          messageFromError(e)
        );
      }
    };

  const load = async (
    p = page
  ) => {
    setLoading(true);
    setError('');

    try {
      const r =
        await api.get(
          '/transactions',
          {
            params: {
              ...filters,

              search:
                filters.search ||
                undefined,

              status:
                filters.status ||
                undefined,

              type:
                filters.type ||
                undefined,

              page: p,
            },
          }
        );

      setItems(
        unwrap(r) || []
      );

      setPagination(
        r.data.pagination ||
          emptyPagination
      );
    } catch (e) {
      setError(
        messageFromError(e)
      );
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadProducts();
    load(1);
  }, []);

  useEffect(() => {
    load(page);
  }, [
    page,
    filters.status,
    filters.type,
    filters.per_page,
  ]);

  useEffect(() => {
    const t = setTimeout(() => {
      setPage(1);
      load(1);
    }, 400);

    return () =>
      clearTimeout(t);
  }, [filters.search]);

  const setDetailRow = (
    idx,
    key,
    val
  ) => {
    setForm((f) => ({
      ...f,

      details: f.details.map(
        (d, i) =>
          i === idx
            ? {
                ...d,
                [key]: val,
              }
            : d
      ),
    }));
  };

  const addRow = () => {
    setForm((f) => ({
      ...f,

      details: [
        ...f.details,
        {
          product_id: '',
          quantity: 1,
        },
      ],
    }));
  };

  const removeRow = (idx) => {
    setForm((f) => ({
      ...f,

      details: f.details.filter(
        (_, i) => i !== idx
      ),
    }));
  };

  const submit = async (e) => {
    e.preventDefault();

    setLoading(true);
    setError('');

    try {
      const payload = {
        type: form.type,

        note:
          form.note ||
          undefined,

        details:
          form.details.map(
            (d) => ({
              product_id:
                Number(
                  d.product_id
                ),

              quantity:
                Number(
                  d.quantity
                ),
            })
          ),
      };

      if (modal === 'create') {
        await api.post(
          '/transactions',
          payload
        );
      } else {
        await api.put(
          `/transactions/${form.id}`,
          payload
        );
      }

     showToast(
  modal === 'create'
    ? 'Transaksi draft berhasil ditambahkan!'
    : 'Transaksi draft berhasil diperbarui!',
  'success'
);

      setModal(null);

      await load(page);
    } catch (e) {
      const errorMessage = messageFromError(e);
      setError(errorMessage);
      showToast(errorMessage, 'error');
    } finally {
      setLoading(false);
    }
  };

  const action = async (
    id,
    path,
    msg
  ) => {
    if (!confirm(msg)) {
      return;
    }

    setError('');

    try {
      const r =
        await api.post(
          `/transactions/${id}/${path}`
        );

      showToast(
        r.data.message,
        'success'
      );

      await load(page);
    } catch (e) {
      const errorMessage = messageFromError(e);
      setError(errorMessage);
      showToast(errorMessage, 'error');
    }
  };

  const remove = async (id) => {
    if (
      !confirm(
        'Hapus draft transaksi ini?'
      )
    ) {
      return;
    }

    try {
      await api.delete(
        `/transactions/${id}`
      );

      setSuccess(
        'Draft berhasil dihapus.'
      );

      await load(page);
    } catch (e) {
      setError(
        messageFromError(e)
      );
    }
  };

  const openDetail = async (
    id
  ) => {
    try {
      const r =
        await api.get(
          `/transactions/${id}`
        );

      setDetail(
        unwrap(r)
      );
    } catch (e) {
      setError(
        messageFromError(e)
      );
    }
  };

  const totalPreview =
    useMemo(
      () =>
        form.details.reduce(
          (sum, d) => {
            const p =
              products.find(
                (x) =>
                  Number(x.id) ===
                  Number(
                    d.product_id
                  )
              );

            return (
              sum +
              Number(
                p?.price || 0
              ) *
                Number(
                  d.quantity || 0
                )
            );
          },
          0
        ),
      [form, products]
    );

  return (
    <>
      <PageTitle
        title="Transactions"
        subtitle="Draft, complete, cancel, detail, transaction log, dan stock validation."
        action={
          <button
            className="primary"
            onClick={() => {
              setForm({
                type: 'IN',
                note: '',
                details: [
                  {
                    product_id: '',
                    quantity: 1,
                  },
                ],
              });

              setModal('create');
            }}
          >
            + Buat Transaksi
          </button>
        }
      />

      {error && (
        <Alert>
          {error}
        </Alert>
      )}

      <section className="card">
        <div className="toolbar">
          <input
            className="search"
            placeholder="Cari kode transaksi..."
            value={
              filters.search
            }
            onChange={(e) =>
              setFilters({
                ...filters,
                search:
                  e.target.value,
              })
            }
          />

          <select
            value={filters.type}
            onChange={(e) => {
              setFilters({
                ...filters,
                type:
                  e.target.value,
              });

              setPage(1);
            }}
          >
            <option value="">
              Semua tipe
            </option>

            <option value="IN">
              IN
            </option>

            <option value="OUT">
              OUT
            </option>
          </select>

          <select
            value={filters.status}
            onChange={(e) => {
              setFilters({
                ...filters,
                status:
                  e.target.value,
              });

              setPage(1);
            }}
          >
            <option value="">
              Semua status
            </option>

            <option value="draft">
              Draft
            </option>

            <option value="completed">
              Completed
            </option>

            <option value="cancelled">
              Cancelled
            </option>
          </select>

          <select
            value={
              filters.per_page
            }
            onChange={(e) => {
              setFilters({
                ...filters,
                per_page:
                  Number(
                    e.target.value
                  ),
              });

              setPage(1);
            }}
          >
            <option>10</option>
            <option>25</option>
            <option>50</option>
            <option>100</option>
          </select>
        </div>

        {loading ? (
          <p>
            Memuat transaksi...
          </p>
        ) : (
          <div className="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Kode</th>
                  <th>Tipe</th>
                  <th>Status</th>
                  <th>Total</th>
                  <th>Tanggal</th>
                  <th>Aksi</th>
                </tr>
              </thead>

              <tbody>
                {items.length === 0 ? (
                  <tr>
                    <td colSpan="7">
                      Tidak ada transaksi.
                    </td>
                  </tr>
                ) : (
                  items.map((t) => (
                    <tr key={t.id}>
                      <td>{t.id}</td>

                      <td>
                        {t.transaction_code}
                      </td>

                      <td>
                        <span
                          className={`badge ${t.type.toLowerCase()}`}
                        >
                          {t.type}
                        </span>
                      </td>

                      <td>
                        <span
                          className={`badge ${t.status}`}
                        >
                          {t.status}
                        </span>
                      </td>

                      <td>
                        {formatRupiah(
                          t.total
                        )}
                      </td>

                      <td>
                        {formatDate(
                          t.created_at
                        )}
                      </td>

                      <td className="actions">
                        <button
                          onClick={() =>
                            openDetail(
                              t.id
                            )
                          }
                        >
                          Detail
                        </button>

                        {t.status ===
                          'draft' && (
                          <>
                            <button
                              onClick={() => {
                                setForm({
                                  id: t.id,
                                  type: t.type,
                                  note:
                                    t.note ||
                                    '',
                                  details:
                                    (
                                      t.details ||
                                      []
                                    ).map(
                                      (d) => ({
                                        product_id:
                                          d.product_id,

                                        quantity:
                                          d.quantity,
                                      })
                                    ),
                                });

                                setModal(
                                  'edit'
                                );
                              }}
                            >
                              Edit
                            </button>

                            <button
                              onClick={() =>
                                action(
                                  t.id,
                                  'complete',
                                  'Complete transaksi ini?'
                                )
                              }
                            >
                              Complete
                            </button>

                            <button
                              onClick={() =>
                                action(
                                  t.id,
                                  'cancel',
                                  'Cancel transaksi draft ini?'
                                )
                              }
                            >
                              Cancel
                            </button>

                            <button
                              className="danger-text"
                              onClick={() =>
                                remove(
                                  t.id
                                )
                              }
                            >
                              Hapus
                            </button>
                          </>
                        )}
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        )}

        <Pagination
          pagination={pagination}
          onPage={setPage}
        />
      </section>

      {modal && (
        <Modal
          title={
            modal === 'create'
              ? 'Buat Transaksi Draft'
              : 'Edit Draft Transaksi'
          }
          onClose={() =>
            setModal(null)
          }
        >
          <form onSubmit={submit}>
            <SelectSimple
              label="Tipe"
              value={form.type}
              onChange={(v) =>
                setForm({
                  ...form,
                  type: v,
                })
              }
              options={[
                {
                  id: 'IN',
                  name: 'IN (Barang Masuk)',
                },
                {
                  id: 'OUT',
                  name: 'OUT (Barang Keluar)',
                },
              ]}
            />

            {form.details.map(
              (d, i) => (
                <div
                  className="detail-row"
                  key={i}
                >
                  <SelectField
                    label={`Product #${i + 1}`}
                    value={
                      d.product_id
                    }
                    onChange={(v) =>
                      setDetailRow(
                        i,
                        'product_id',
                        v
                      )
                    }
                    options={products}
                    required
                  />

                  <Field
                    label="Qty"
                    type="number"
                    value={
                      d.quantity
                    }
                    onChange={(v) =>
                      setDetailRow(
                        i,
                        'quantity',
                        v
                      )
                    }
                    required
                  />

                  {form.details
                    .length > 1 && (
                    <button
                      type="button"
                      className="danger-text"
                      onClick={() =>
                        removeRow(i)
                      }
                    >
                      Hapus baris
                    </button>
                  )}
                </div>
              )
            )}

            <button
              type="button"
              onClick={addRow}
            >
              + Tambah Product
            </button>

            <Field
              label="Catatan"
              value={
                form.note
              }
              onChange={(v) =>
                setForm({
                  ...form,
                  note: v,
                })
              }
              textarea
            />

            <div className="total-preview">
              Estimasi total:{' '}
              <strong>
                {formatRupiah(
                  totalPreview
                )}
              </strong>
            </div>

            <div className="form-actions">
              <button
                type="button"
                onClick={() =>
                  setModal(null)
                }
              >
                Batal
              </button>

              <button
                className="primary"
                disabled={loading}
              >
                {loading
                  ? 'Menyimpan...'
                  : 'Simpan Draft'}
              </button>
            </div>
          </form>
        </Modal>
      )}

      {detail && (
        <Modal
          title={`Detail ${detail.transaction_code}`}
          onClose={() =>
            setDetail(null)
          }
        >
          <div className="detail-summary">
            <p>
              <b>Tipe:</b>{' '}
              {detail.type}
            </p>

            <p>
              <b>Status:</b>{' '}
              {detail.status}
            </p>

            <p>
              <b>Total:</b>{' '}
              {formatRupiah(
                detail.total
              )}
            </p>

            <p>
              <b>Catatan:</b>{' '}
              {detail.note || '-'}
            </p>
          </div>

          <div className="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Qty</th>
                  <th>Harga</th>
                  <th>Subtotal</th>
                </tr>
              </thead>

              <tbody>
                {(detail.details ||
                  []).map((d) => (
                  <tr key={d.id}>
                    <td>
                      {d.product?.name ||
                        `Product #${d.product_id}`}
                    </td>

                    <td>
                      {d.quantity}
                    </td>

                    <td>
                      {formatRupiah(
                        d.price
                      )}
                    </td>

                    <td>
                      {formatRupiah(
                        d.subtotal
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <h4>
            Transaction Log
          </h4>

          {(detail.logs || []).map(
            (l) => (
              <div
                className="log-item"
                key={l.id}
              >
                {l.old_status} →{' '}
                {l.new_status} —{' '}
                {l.note || '-'} —{' '}
                {formatDate(
                  l.created_at
                )}
              </div>
            )
          )}
        </Modal>
      )}
    </>
  );
}

function SelectSimple({
  label,
  value,
  onChange,
  options,
}) {
  return (
    <label className="field">
      <span>{label}</span>

      <select
        value={value}
        onChange={(e) =>
          onChange(
            e.target.value
          )
        }
      >
        {options.map((o) => (
          <option
            key={o.id}
            value={o.id}
          >
            {o.name}
          </option>
        ))}
      </select>
    </label>
  );
}

/*
|--------------------------------------------------------------------------
| REPORT PAGE
|--------------------------------------------------------------------------
| Preview laporan sebelum print/export.
|--------------------------------------------------------------------------
*/

function ReportPage() {
  const today = getLocalDateString();

  const currentYear =
    new Date().getFullYear();

  const [mode, setMode] =
    useState('period');

  const [date, setDate] =
    useState(today);

  const [dateFrom, setDateFrom] =
    useState(
      `${currentYear}-01-01`
    );

  const [dateTo, setDateTo] =
    useState(today);

  const [view, setView] =
    useState('summary');

  const [type, setType] =
    useState('');

  const [report, setReport] =
    useState(null);

  const [statistics, setStatistics] =
    useState(null);

  const [loading, setLoading] =
    useState(false);

  const [error, setError] =
    useState('');

  const loadDaily = async () => {
    if (!date) {
      setError(
        'Tanggal laporan wajib dipilih.'
      );

      return;
    }

    setLoading(true);
    setError('');

    try {
      const r = await api.get(
        '/reports/daily',
        {
          params: {
            date,
          },
        }
      );

      setReport(
        unwrap(r)
      );

      setStatistics(null);
    } catch (e) {
      setError(
        messageFromError(e)
      );

      setReport(null);
      setStatistics(null);
    } finally {
      setLoading(false);
    }
  };

  const loadPeriod = async () => {
    if (!dateFrom || !dateTo) {
      setError(
        'Tanggal awal dan tanggal akhir wajib diisi.'
      );

      return;
    }

    if (dateFrom > dateTo) {
      setError(
        'Tanggal awal tidak boleh lebih besar dari tanggal akhir.'
      );

      return;
    }

    setLoading(true);
    setError('');

    try {
      const [
        reportResponse,
        statisticsResponse,
      ] = await Promise.all([
        api.get('/reports/period', {
          params: {
            date_from: dateFrom,
            date_to: dateTo,
            view,
            type: type || undefined,
          },
        }),

        api.get('/reports/statistics', {
          params: {
            date_from: dateFrom,
            date_to: dateTo,
            type: type || undefined,
          },
        }),
      ]);

      setReport(
        unwrap(reportResponse)
      );

      setStatistics(
        unwrap(statisticsResponse)
      );
    } catch (e) {
      setError(
        messageFromError(e)
      );

      setReport(null);
      setStatistics(null);
    } finally {
      setLoading(false);
    }
  };

  const load = async () => {
    if (mode === 'daily') {
      await loadDaily();
    } else {
      await loadPeriod();
    }
  };


  
const exportReport = async (format) => {
  if (mode !== 'period' || !report) {
    setError('Export hanya tersedia untuk laporan periode.');
    window.alert('Export hanya tersedia untuk laporan periode.');
    return;
  }

  setError('');

  try {
    const response = await api.get(
      `/reports/export/${format}`,
      {
        params: {
          date_from: dateFrom,
          date_to: dateTo,
          view,
          type: type || undefined,
        },
        responseType: 'blob',
      }
    );

    const blob = new Blob(
      [response.data],
      {
        type: format === 'pdf'
          ? 'application/pdf'
          : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      }
    );

    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');

    link.href = url;
    link.download = `laporan-${dateFrom}-sampai-${dateTo}.${format === 'pdf' ? 'pdf' : 'xlsx'}`;

    document.body.appendChild(link);
    link.click();
    link.remove();

    window.URL.revokeObjectURL(url);

    if (format === 'pdf') {
      window.alert('Laporan PDF berhasil diunduh!');
    } else {
      window.alert('Laporan Excel berhasil diunduh!');
    }

  } catch (e) {
    setError('Gagal mengunduh laporan. Silakan coba lagi.');
    window.alert('Gagal mengunduh laporan. Silakan coba lagi.');

    console.error('Export error:', e);
  }
};


const summary =
  report?.summary || null;

const transactions =
  Array.isArray(report?.transactions)
    ? report.transactions
    : Array.isArray(report?.transactions?.data)
      ? report.transactions.data
      : [];

  /*
  |--------------------------------------------------------------------------
  | PRINT REPORT
  |--------------------------------------------------------------------------
  | Hanya bagian report yang dicetak.
  |--------------------------------------------------------------------------
  */

  const printReport = () => {
    const reportElement =
      document.getElementById(
        'report-print-area'
      );

    if (!reportElement) {
      return;
    }

    const printWindow =
      window.open(
        '',
        '_blank',
        'width=1100,height=800'
      );

    if (!printWindow) {
      setError(
        'Popup diblokir browser. Izinkan popup untuk mencetak laporan.'
      );

      return;
    }

    printWindow.document.write(`
      <!DOCTYPE html>

      <html lang="id">
        <head>
          <meta charset="UTF-8" />

          <title>
            Laporan Inventory Stock Management
          </title>

          <style>
            * {
              box-sizing: border-box;
            }

            body {
              margin: 0;
              padding: 30px;
              font-family:
                Arial,
                Helvetica,
                sans-serif;

              color: #111827;
              background: #ffffff;
            }

            h1,
            h2,
            h3,
            h4,
            p {
              margin-top: 0;
            }

            h2 {
              margin-bottom: 6px;
            }

            h3 {
              margin-top: 24px;
              margin-bottom: 12px;
            }

            .report-header {
              display: flex;
              justify-content: space-between;
              gap: 20px;

              padding-bottom: 18px;
              margin-bottom: 24px;

              border-bottom:
                2px solid #111827;
            }

            .report-header p {
              margin-bottom: 5px;
              color: #4b5563;
            }

            .report-status {
              border: 1px solid #9ca3af;
              border-radius: 6px;
              padding: 6px 12px;

              height: fit-content;
              font-size: 12px;
            }

            .stats-grid {
              display: grid;

              grid-template-columns:
                repeat(4, minmax(0, 1fr));

              gap: 12px;
              margin-bottom: 24px;
            }

            .stat-card {
              border: 1px solid #d1d5db;
              border-radius: 8px;

              padding: 14px;
            }

            .stat-card span {
              display: block;
              color: #6b7280;
              font-size: 12px;

              margin-bottom: 7px;
            }

            .stat-card strong {
              display: block;
              font-size: 18px;
            }

            .report-statistics {
              border:
                1px solid #d1d5db;

              padding: 18px;
              margin-bottom: 24px;
            }

            table {
              width: 100%;
              border-collapse: collapse;
              margin-bottom: 20px;
            }

            th,
            td {
              border:
                1px solid #d1d5db;

              padding: 8px;

              text-align: left;

              font-size: 12px;

              vertical-align: top;
            }

            th {
              background: #f3f4f6;
              font-weight: 700;
            }

            .badge {
              display: inline-block;

              padding:
                3px 8px;

              border:
                1px solid #9ca3af;

              border-radius:
                999px;

              font-size:
                11px;
            }

            .report-footer {
              display: flex;
              justify-content: space-between;

              border-top:
                1px solid #d1d5db;

              margin-top: 30px;
              padding-top: 12px;

              color: #6b7280;
              font-size: 11px;
            }

            @media print {
              body {
                padding: 15px;
              }

              .report-statistics {
                break-inside: avoid;
              }

              table {
                break-inside: auto;
              }

              tr {
                break-inside: avoid;
                break-after: auto;
              }
            }
          </style>
        </head>

        <body>
          ${reportElement.innerHTML}
        </body>
      </html>
    `);

    printWindow.document.close();
    printWindow.focus();

    setTimeout(() => {
      printWindow.print();
      printWindow.close();
    }, 300);
  };

  return (
    <>
      <PageTitle
        title="Reports"
        subtitle="Preview laporan sebelum print atau export."
        action={
          <div className="actions">
            <button
              onClick={load}
              disabled={loading}
            >
              {loading
                ? 'Memuat...'
                : 'Refresh'}
            </button>

            <button
              className="primary"
              onClick={printReport}
              disabled={!report}
            >
              🖨 Print
            </button>

             <button
    onClick={() => exportReport('pdf')}
    disabled={!report || mode !== 'period'}
  >
    📄 Export PDF
  </button>

  <button
    onClick={() => exportReport('excel')}
    disabled={!report || mode !== 'period'}
  >
    📊 Export Excel
  </button>
          </div>
        }
      />

      {error && (
        <Alert>
          {error}
        </Alert>
      )}

      <section className="card report-filter">
        <h3>
          Filter Laporan
        </h3>

        <div className="filter-grid">
          <select
            value={mode}
            onChange={(e) => {
              setMode(
                e.target.value
              );

              setReport(null);
              setStatistics(null);
              setError('');
            }}
          >
            <option value="period">
              Laporan Periode
            </option>

            <option value="daily">
              Laporan Harian
            </option>
          </select>

          {mode === 'daily' ? (
            <label>
              Tanggal

              <input
                type="date"
                value={date}
                onChange={(e) =>
                  setDate(
                    e.target.value
                  )
                }
              />
            </label>
          ) : (
            <>
              <label>
                Dari

                <input
                  type="date"
                  value={dateFrom}
                  onChange={(e) =>
                    setDateFrom(
                      e.target.value
                    )
                  }
                />
              </label>

              <label>
                Sampai

                <input
                  type="date"
                  value={dateTo}
                  onChange={(e) =>
                    setDateTo(
                      e.target.value
                    )
                  }
                />
              </label>

              <select
                value={view}
                onChange={(e) =>
                  setView(
                    e.target.value
                  )
                }
              >
                <option value="summary">
                  Summary
                </option>

                <option value="detail">
                  Detail
                </option>
              </select>

              <select
                value={type}
                onChange={(e) =>
                  setType(
                    e.target.value
                  )
                }
              >
                <option value="">
                  Semua tipe
                </option>

                <option value="IN">
                  IN
                </option>

                <option value="OUT">
                  OUT
                </option>
              </select>
            </>
          )}

          <button
            className="primary"
            onClick={load}
            disabled={loading}
          >
            {loading
              ? 'Memuat...'
              : 'Tampilkan Laporan'}
          </button>
        </div>
      </section>

      {loading && (
  <div className="loading-message">
    Memuat laporan...
  </div>
)}


      {loading ? (
        <section className="card">
          <p>
            Memuat preview laporan...
          </p>
        </section>
      ) : report ? (
        <section
          className="report-preview"
          id="report-print-area"
        >
          <div className="card">
            <div className="report-header">
              <div>
                <h2>
                  Laporan Inventory
                </h2>

                <p>
                  {mode === 'daily'
                    ? `Tanggal: ${report.date}`
                    : `Periode: ${report.date_from} s/d ${report.date_to}`}
                </p>

                {mode === 'period' && (
                  <>
                    <p>
                      Tipe:{' '}
                      {report.filter?.type ===
                      'all'
                        ? 'Semua'
                        : report.filter?.type ||
                          'Semua'}
                    </p>

                    <p>
                      Tampilan:{' '}
                      {view === 'summary'
                        ? 'Summary'
                        : 'Detail'}
                    </p>
                  </>
                )}
              </div>

              <div className="report-status">
                Preview
              </div>
            </div>

            {summary && (
              <>
                <h3>
                  Ringkasan
                </h3>

                <div className="stats-grid">
                  <Stat
                    title="Total Transaksi"
                    value={
                      summary.total_transactions
                    }
                  />

                  <Stat
                    title="Total IN"
                    value={
                      summary.total_in
                    }
                  />

                  <Stat
                    title="Total OUT"
                    value={
                      summary.total_out
                    }
                  />

                  <Stat
                    title="Total Nilai"
                    value={formatRupiah(
                      summary.total_value
                    )}
                  />

                  {mode === 'period' && (
                    <>
                      <Stat
                        title="Rata-rata"
                        value={formatRupiah(
                          summary.average_value
                        )}
                      />

                      <Stat
                        title="Nilai Tertinggi"
                        value={formatRupiah(
                          summary.highest_value
                        )}
                      />

                      <Stat
                        title="Nilai Terendah"
                        value={formatRupiah(
                          summary.lowest_value
                        )}
                      />
                    </>
                  )}
                </div>
              </>
            )}

            {statistics && (
              <div className="card report-statistics">
                <h3>
                  Statistik Periode
                </h3>

                <div className="stats-grid">
                  <Stat
                    title="Transaksi Periode Sekarang"
                    value={
                      statistics.current_period
                        ?.total_transactions
                    }
                  />

                  <Stat
                    title="Nilai Periode Sekarang"
                    value={formatRupiah(
                      statistics.current_period
                        ?.total_value
                    )}
                  />

                  <Stat
                    title="Transaksi Periode Sebelumnya"
                    value={
                      statistics.previous_period
                        ?.total_transactions
                    }
                  />

                  <Stat
                    title="Nilai Periode Sebelumnya"
                    value={formatRupiah(
                      statistics.previous_period
                        ?.total_value
                    )}
                  />

                  <Stat
                    title="Perubahan Nilai"
                    value={
                      statistics.percentage_change ===
                      null ||
                      statistics.percentage_change ===
                        undefined
                        ? 'N/A'
                        : `${Number(
                            statistics.percentage_change
                          ).toLocaleString(
                            'id-ID',
                            {
                              maximumFractionDigits: 2,
                            }
                          )}%`
                    }
                  />
                </div>
              </div>
            )}

            <h3>
              Detail Transaksi
            </h3>

            {transactions.length === 0 ? (
              <p>
                Tidak ada transaksi pada laporan ini.
              </p>
            ) : (
              <div className="table-wrap">
                <table>
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Kode</th>
                      <th>User</th>
                      <th>Tipe</th>
                      <th>Status</th>
                      <th>Total</th>
                      <th>Tanggal</th>
                    </tr>
                  </thead>

                  <tbody>
                    {transactions.map(
                      (transaction) => (
                        <tr
                          key={
                            transaction.id
                          }
                        >
                          <td>
                            {transaction.id}
                          </td>

                          <td>
                            {
                              transaction.transaction_code
                            }
                          </td>

                          <td>
                            {transaction.user
                              ?.name ||
                              '-'}
                          </td>

                          <td>
                            <span
                              className={`badge ${transaction.type?.toLowerCase()}`}
                            >
                              {
                                transaction.type
                              }
                            </span>
                          </td>

                          <td>
                            <span
                              className={`badge ${transaction.status}`}
                            >
                              {
                                transaction.status
                              }
                            </span>
                          </td>

                          <td>
                            {formatRupiah(
                              transaction.total
                            )}
                          </td>

                          <td>
                            {formatDate(
                              transaction.created_at
                            )}
                          </td>
                        </tr>
                      )
                    )}
                  </tbody>
                </table>
              </div>
            )}

            {mode === 'period' &&
              view === 'detail' &&
              transactions.length > 0 && (
                <>
                  <h3>
                    Detail Product
                  </h3>

                  <div className="table-wrap">
                    <table>
                      <thead>
                        <tr>
                          <th>
                            Transaksi
                          </th>

                          <th>
                            Product
                          </th>

                          <th>
                            SKU
                          </th>

                          <th>
                            Qty
                          </th>

                          <th>
                            Harga
                          </th>

                          <th>
                            Subtotal
                          </th>
                        </tr>
                      </thead>

                      <tbody>
                        {transactions.flatMap(
                          (transaction) =>
                            (
                              transaction.details ||
                              []
                            ).map(
                              (detail) => (
                                <tr
                                  key={`${transaction.id}-${detail.id}`}
                                >
                                  <td>
                                    {
                                      transaction.transaction_code
                                    }
                                  </td>

                                  <td>
                                    {detail
                                      .product
                                      ?.name ||
                                      `Product #${detail.product_id}`}
                                  </td>

                                  <td>
                                    {detail
                                      .product
                                      ?.sku ||
                                      '-'}
                                  </td>

                                  <td>
                                    {
                                      detail.quantity
                                    }
                                  </td>

                                  <td>
                                    {formatRupiah(
                                      detail.price
                                    )}
                                  </td>

                                  <td>
                                    {formatRupiah(
                                      detail.subtotal
                                    )}
                                  </td>
                                </tr>
                              )
                            )
                        )}
                      </tbody>
                    </table>
                  </div>
                </>
              )}

            <div className="report-footer">
              <span>
                Inventory Stock Management
              </span>

              <span>
                Preview laporan
              </span>
            </div>
          </div>
        </section>
      ) : (
        <section className="card">
          <p>
            Silakan pilih parameter laporan lalu klik{' '}
            <strong>
              Tampilkan Laporan
            </strong>
            .
          </p>
        </section>
      )}
    </>
  );
}

/*
|--------------------------------------------------------------------------
| PROFILE
|--------------------------------------------------------------------------
*/

function ProfilePage({ user }) {
  return (
    <>
      <PageTitle
        title="Profile"
        subtitle="Informasi user yang sedang login."
      />

      <section className="card profile-card">
        <div>
          <span>Nama</span>

          <strong>
            {user?.name}
          </strong>
        </div>

        <div>
          <span>Email</span>

          <strong>
            {user?.email}
          </strong>
        </div>

        <div>
          <span>Role</span>

          <strong>
            {user?.role}
          </strong>
        </div>
      </section>
    </>
  );
}

/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

function LoginPage({ onSuccess }) {
  const [mode, setMode] =
    useState('login');

  const [email, setEmail] =
    useState('');

  const [password, setPassword] =
    useState('');

  const [name, setName] =
    useState('');

  const [loading, setLoading] =
    useState(false);

  const [error, setError] =
    useState('');

  const [success, setSuccess] =
    useState('');

  const submit = async (e) => {
    e.preventDefault();

    setLoading(true);
    setError('');
    setSuccess('');

    try {
      if (mode === 'login') {
        const data =
          await login(
            email,
            password
          );

        const token =
          data.token ||
          data.access_token ||
          data.data?.token ||
          data.data?.access_token;

        if (!token) {
          throw new Error(
            'Token tidak ditemukan pada response login.'
          );
        }

        localStorage.setItem(
          'token',
          token
        );

        onSuccess(token);
      } else {
        await register(
          name,
          email,
          password
        );

        setSuccess(
          'Registrasi berhasil. Silakan login.'
        );

        setMode('login');
        setPassword('');
      }
    } catch (e) {
      setError(
        messageFromError(e)
      );
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="auth-page">
      <div className="auth-card">
        <div className="brand-mark">
          ISM
        </div>

        <h1>
          Inventory Stock Management
        </h1>

        <p>
          {mode === 'login'
            ? 'Login ke sistem'
            : 'Registrasi user baru'}
        </p>

        {error && (
          <Alert>
            {error}
          </Alert>
        )}

        {success && (
          <Alert type="success">
            {success}
          </Alert>
        )}

        <form onSubmit={submit}>
          {mode === 'register' && (
            <Field
              label="Nama"
              value={name}
              onChange={setName}
              required
            />
          )}

          <Field
            label="Email"
            type="email"
            value={email}
            onChange={setEmail}
            required
          />

          <Field
            label="Password"
            type="password"
            value={password}
            onChange={setPassword}
            required
          />

          <button
            className="primary full"
            disabled={loading}
          >
            {loading
              ? 'Memproses...'
              : mode === 'login'
                ? 'Login'
                : 'Register'}
          </button>
        </form>

        <button
          className="link-button"
          onClick={() => {
            setMode(
              mode === 'login'
                ? 'register'
                : 'login'
            );

            setError('');
            setSuccess('');
          }}
        >
          {mode === 'login'
            ? 'Belum punya akun? Register'
            : 'Sudah punya akun? Login'}
        </button>
      </div>
    </div>
  );
}

/*
|--------------------------------------------------------------------------
| APP
|--------------------------------------------------------------------------
*/

function App() {
  const [token, setToken] =
    useState(
      localStorage.getItem(
        'token'
      )
    );

  const [user, setUser] =
    useState(null);

  const [view, setView] =
    useState('dashboard');

  const [viewHistory, setViewHistory] = useState([]);

  const [booting, setBooting] =
    useState(Boolean(token));

  const [authError, setAuthError] =
    useState('');

  const [showBackToTop, setShowBackToTop] =
    useState(false);

  const [toast, setToast] = useState(null);

  useEffect(() => {
    const handleToast = (event) => {
      setToast(event.detail);
    };

    window.addEventListener('app-toast', handleToast);

    return () => {
      window.removeEventListener('app-toast', handleToast);
    };
  }, []);

  useEffect(() => {
    if (!toast) return undefined;

    const timeoutId = window.setTimeout(() => {
      setToast(null);
    }, 3000);

    return () => window.clearTimeout(timeoutId);
  }, [toast]);

  const scrollToTop = () => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth',
    });
  };

  const navigateTo = (nextView) => {
    if (nextView === view) return;

    setViewHistory((prev) => [...prev, view]);
    setView(nextView);
    scrollToTop();
  };

  const goBack = () => {
    if (viewHistory.length === 0) return;

    const previousView = viewHistory[viewHistory.length - 1];

    setViewHistory((prev) => prev.slice(0, -1));
    setView(previousView);
    scrollToTop();
  };

  useEffect(() => {
    const handleScroll = () => {
      setShowBackToTop(
        window.scrollY > 300
      );
    };

    window.addEventListener(
      'scroll',
      handleScroll,
      { passive: true }
    );

    handleScroll();

    return () => {
      window.removeEventListener(
        'scroll',
        handleScroll
      );
    };
  }, []);


  useEffect(() => {
    if (!token) {
      setBooting(false);
      return;
    }

    api.get('/profile')
      .then((r) => {
        setUser(
          r.data.user
        );

        setBooting(false);
      })
      .catch((e) => {
        localStorage.removeItem(
          'token'
        );

        setToken(null);
        setBooting(false);

        setAuthError(
          messageFromError(e)
        );
      });
  }, [token]);

  const logout = async () => {
    try {
      await api.post('/logout');
    } catch {}

    localStorage.removeItem(
      'token'
    );

    setToken(null);
    setUser(null);
    setView('dashboard');
    setViewHistory([]);
  };

  if (!token) {
    return (
      <LoginPage
        onSuccess={setToken}
      />
    );
  }

  if (booting) {
    return (
      <div className="loading-screen">
        Memuat session...
      </div>
    );
  }

  if (authError) {
    return (
      <LoginPage
        onSuccess={setToken}
      />
    );
  }

  const admin =
    user?.role ===
    'administrator';

  const nav = [
  {
    id: 'dashboard',
    label: 'Dashboard',
    icon: '⌂',
    section: 'UTAMA',
  },
  
  {
    id: 'reports',
    label: 'Reports',
    icon: '▥',
    section: 'UTAMA',
  },

  ...(admin
    ? [
        {
          id: 'products',
          label: 'Products',
          icon: '▣',
          section: 'INVENTORY',
        },
        {
          id: 'categories',
          label: 'Categories',
          icon: '◫',
          section: 'INVENTORY',
        },
        {
          id: 'suppliers',
          label: 'Suppliers',
          icon: '♙',
          section: 'INVENTORY',
        },
        {
          id: 'warehouses',
          label: 'Warehouses',
          icon: '⌂',
          section: 'INVENTORY',
        },
      ]
    : []),

  ...(admin
    ? [
        {
          id: 'stock',
          label: 'Stock Management',
          icon: '↕',
          section: 'INVENTORY',
        },
      ]
    : []),

  {
    id: 'history',
    label: 'Stock History',
    icon: '▤',
    section: 'INVENTORY',
  },
  {
    id: 'transactions',
    label: 'Transactions',
    icon: '⇄',
    section: 'TRANSAKSI',
  },
  {
    id: 'profile',
    label: 'Profile',
    icon: '◉',
    section: 'AKUN',
  },
];

const currentPage = nav.find(
  (item) => item.id === view
);

  let content;

  if (
    view === 'dashboard'
  ) {
    content = (
      <Dashboard
        user={user}
      />
    );
  } else if (
    view === 'reports'
  ) {
    content = (
      <ReportPage />
    );
  } else if (
    view === 'products'
  ) {
    content = (
      <ProductPage
        canManage={admin}
      />
    );
  } else if (
    [
      'categories',
      'suppliers',
      'warehouses',
    ].includes(view)
  ) {
    content = (
      <MasterPage
        type={view}
        canManage={admin}
      />
    );
  } else if (
    view === 'stock'
  ) {
    content = (
      <StockPage />
    );
  } else if (
    view === 'history'
  ) {
    content = (
      <StockHistoryPage />
    );
  } else if (
    view === 'transactions'
  ) {
    content = (
      <TransactionsPage />
    );
  } else {
    content = (
      <ProfilePage
        user={user}
      />
    );
  }

  
  return (
    <div className="app-shell">
      <aside className="sidebar">
        <div className="sidebar-brand">
          <div className="brand-mark small">
            ISM
          </div>

          <div>
            <strong>Inventory</strong>
            <span>Stock Management</span>
          </div>
        </div>

        <div className="user-mini">
          <strong>{user?.name}</strong>
          <span>{user?.role}</span>
        </div>

        <nav>
  {nav.map((n, index) => (
    <div className="nav-entry" key={n.id}>
      {(index === 0 || nav[index - 1].section !== n.section) && (
        <div className="nav-section-title">
          {n.section}
        </div>
      )}

      <button
        type="button"
        className={view === n.id ? 'nav-item active' : 'nav-item'}
        onClick={() => navigateTo(n.id)} 
      >
        <span>{n.icon}</span>
        {n.label}
      </button>
    </div>
  ))}
</nav>

        <button
          type="button"
          className="logout-button"
          onClick={logout}
        >
          Logout
        </button>

        <div className="page-navigation">
          <button
            type="button"
            className="breadcrumb-back"
            onClick={goBack}
            disabled={viewHistory.length === 0}
          >
            ← Kembali
          </button>

          <div className="breadcrumb">
            <span className="breadcrumb-home">
              Beranda
            </span>

            <span className="breadcrumb-separator">
              /
            </span>

            <span className="breadcrumb-current">
              {currentPage?.label || 'Dashboard'}
            </span>
          </div>
        </div>
      </aside>

      <main className="main-content">
  

  {content}
</main>

      {toast && (
        <div
          className={`app-toast app-toast-${toast.type || 'success'}`}
          role={toast.type === 'error' ? 'alert' : 'status'}
        >
          {toast.message}
        </div>
      )}

      {showBackToTop && (
        <button
          type="button"
          className="back-to-top"
          onClick={scrollToTop}
          aria-label="Kembali ke atas"
          title="Kembali ke atas"
        >
          ↑
        </button>
      )}
    </div>
  );

}

export default App;
