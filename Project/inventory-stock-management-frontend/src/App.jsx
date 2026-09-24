import { useEffect, useState } from 'react';
import { login } from './services/auth';
import api from './services/api';

function App() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');

    const [token, setToken] = useState(
        localStorage.getItem('token')
    );

    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    const handleLogin = async (event) => {
        event.preventDefault();

        setLoading(true);
        setError('');

        try {
            const data = await login(email, password);

            const receivedToken =
                data.token ||
                data.access_token ||
                data.data?.token ||
                data.data?.access_token;

            if (!receivedToken) {
                throw new Error(
                    'Token tidak ditemukan pada response login.'
                );
            }

            localStorage.setItem('token', receivedToken);
            setToken(receivedToken);
        } catch (err) {
            console.error(err);

            setError(
                err.response?.data?.message ||
                err.message ||
                'Login gagal.'
            );
        } finally {
            setLoading(false);
        }
    };

    const getProducts = async () => {
        setLoading(true);
        setError('');

        try {
            const response = await api.get('/products');

            console.log('Products response:', response.data);

            const payload = response.data?.data;

            const productData = Array.isArray(payload)
                ? payload
                : payload?.data ?? [];

            setProducts(productData);
        } catch (err) {
            console.error(err);

            setError(
                err.response?.data?.message ||
                'Gagal mengambil data products.'
            );
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (token) {
            getProducts();
        }
    }, [token]);

    const handleLogout = () => {
        localStorage.removeItem('token');

        setToken(null);
        setProducts([]);
    };

    if (!token) {
        return (
            <div
                style={{
                    minHeight: '100vh',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                }}
            >
                <div
                    style={{
                        background: 'white',
                        padding: '40px',
                        borderRadius: '12px',
                        width: '400px',
                        boxShadow:
                            '0 4px 15px rgba(0,0,0,0.1)',
                    }}
                >
                    <h1 style={{ textAlign: 'center' }}>
                        Inventory Stock Management
                    </h1>

                    <p
                        style={{
                            textAlign: 'center',
                            color: '#6b7280',
                            marginBottom: '30px',
                        }}
                    >
                        Login ke sistem
                    </p>

                    <form onSubmit={handleLogin}>
                        <div style={{ marginBottom: '20px' }}>
                            <label>Email</label>

                            <input
                                type="email"
                                value={email}
                                onChange={(event) =>
                                    setEmail(event.target.value)
                                }
                                placeholder="Masukkan email"
                                required
                                style={{
                                    width: '100%',
                                    padding: '12px',
                                    marginTop: '8px',
                                    border: '1px solid #d1d5db',
                                    borderRadius: '6px',
                                }}
                            />
                        </div>

                        <div style={{ marginBottom: '20px' }}>
                            <label>Password</label>

                            <input
                                type="password"
                                value={password}
                                onChange={(event) =>
                                    setPassword(event.target.value)
                                }
                                placeholder="Masukkan password"
                                required
                                style={{
                                    width: '100%',
                                    padding: '12px',
                                    marginTop: '8px',
                                    border: '1px solid #d1d5db',
                                    borderRadius: '6px',
                                }}
                            />
                        </div>

                        {error && (
                            <p
                                style={{
                                    color: '#dc2626',
                                    background: '#fee2e2',
                                    padding: '10px',
                                    borderRadius: '6px',
                                }}
                            >
                                {error}
                            </p>
                        )}

                        <button
                            type="submit"
                            disabled={loading}
                            style={{
                                width: '100%',
                                padding: '12px',
                                border: 'none',
                                borderRadius: '6px',
                                background: '#2563eb',
                                color: 'white',
                                cursor: 'pointer',
                                fontSize: '16px',
                            }}
                        >
                            {loading
                                ? 'Logging in...'
                                : 'Login'}
                        </button>
                    </form>
                </div>
            </div>
        );
    }

    return (
        <div
            style={{
                minHeight: '100vh',
                padding: '30px',
            }}
        >
            <div
                style={{
                    display: 'flex',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                    marginBottom: '30px',
                }}
            >
                <div>
                    <h1>Inventory Stock Management</h1>

                    <p>
                        Product Management
                    </p>
                </div>

                <button onClick={handleLogout}>
                    Logout
                </button>
            </div>

            <div
                style={{
                    background: 'white',
                    padding: '25px',
                    borderRadius: '10px',
                }}
            >
                <div
                    style={{
                        display: 'flex',
                        justifyContent: 'space-between',
                        alignItems: 'center',
                        marginBottom: '20px',
                    }}
                >
                    <h2>Products</h2>

                    <button onClick={getProducts}>
                        Refresh
                    </button>
                </div>

                {loading && (
                    <p>Loading products...</p>
                )}

                {error && (
                    <p
                        style={{
                            color: '#dc2626',
                            background: '#fee2e2',
                            padding: '10px',
                        }}
                    >
                        {error}
                    </p>
                )}

                {!loading &&
                    !error &&
                    products.length === 0 && (
                        <p>
                            Tidak ada data product.
                        </p>
                    )}

                {!loading &&
                    products.length > 0 && (
                        <div
                            style={{
                                overflowX: 'auto',
                            }}
                        >
                            <table
                                style={{
                                    width: '100%',
                                    borderCollapse:
                                        'collapse',
                                }}
                            >
                                <thead>
                                    <tr>
                                        <th
                                            style={{
                                                textAlign:
                                                    'left',
                                                padding:
                                                    '12px',
                                                borderBottom:
                                                    '1px solid #ddd',
                                            }}
                                        >
                                            ID
                                        </th>

                                        <th
                                            style={{
                                                textAlign:
                                                    'left',
                                                padding:
                                                    '12px',
                                                borderBottom:
                                                    '1px solid #ddd',
                                            }}
                                        >
                                            Nama
                                        </th>

                                        <th
                                            style={{
                                                textAlign:
                                                    'left',
                                                padding:
                                                    '12px',
                                                borderBottom:
                                                    '1px solid #ddd',
                                            }}
                                        >
                                            SKU
                                        </th>

                                        <th
                                            style={{
                                                textAlign:
                                                    'left',
                                                padding:
                                                    '12px',
                                                borderBottom:
                                                    '1px solid #ddd',
                                            }}
                                        >
                                            Harga
                                        </th>

                                        <th
                                            style={{
                                                textAlign:
                                                    'left',
                                                padding:
                                                    '12px',
                                                borderBottom:
                                                    '1px solid #ddd',
                                            }}
                                        >
                                            Stok
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    {products.map(
                                        (product) => (
                                            <tr
                                                key={
                                                    product.id
                                                }
                                            >
                                                <td
                                                    style={{
                                                        padding:
                                                            '12px',
                                                        borderBottom:
                                                            '1px solid #eee',
                                                    }}
                                                >
                                                    {
                                                        product.id
                                                    }
                                                </td>

                                                <td
                                                    style={{
                                                        padding:
                                                            '12px',
                                                        borderBottom:
                                                            '1px solid #eee',
                                                    }}
                                                >
                                                    {
                                                        product.name
                                                    }
                                                </td>

                                                <td
                                                    style={{
                                                        padding:
                                                            '12px',
                                                        borderBottom:
                                                            '1px solid #eee',
                                                    }}
                                                >
                                                    {
                                                        product.sku
                                                    }
                                                </td>

                                                <td
                                                    style={{
                                                        padding:
                                                            '12px',
                                                        borderBottom:
                                                            '1px solid #eee',
                                                    }}
                                                >
                                                    Rp{' '}
                                                    {Number(
                                                        product.price
                                                    ).toLocaleString(
                                                        'id-ID'
                                                    )}
                                                </td>

                                                <td
                                                    style={{
                                                        padding:
                                                            '12px',
                                                        borderBottom:
                                                            '1px solid #eee',
                                                    }}
                                                >
                                                    {
                                                        product.stock
                                                    }
                                                </td>
                                            </tr>
                                        )
                                    )}
                                </tbody>
                            </table>
                        </div>
                    )}
            </div>
        </div>
    );
}

export default App;