import { useState } from 'react';
import { login } from './services/auth';

function App() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);
    const [token, setToken] = useState(
        localStorage.getItem('token')
    );

    const handleLogin = async (event) => {
        event.preventDefault();

        setLoading(true);
        setError('');

        try {
            const data = await login(email, password);

            console.log('Login response:', data);

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

    const handleLogout = () => {
        localStorage.removeItem('token');
        setToken(null);
    };

    if (token) {
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
                        textAlign: 'center',
                    }}
                >
                    <h1>Inventory Stock Management</h1>

                    <h2>Login Berhasil</h2>

                    <p>
                        Token Sanctum berhasil diterima.
                    </p>

                    <button onClick={handleLogout}>
                        Logout
                    </button>
                </div>
            </div>
        );
    }

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
                    boxShadow: '0 4px 15px rgba(0,0,0,0.1)',
                }}
            >
                <h1
                    style={{
                        textAlign: 'center',
                        marginBottom: '10px',
                    }}
                >
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
                        {loading ? 'Logging in...' : 'Login'}
                    </button>
                </form>
            </div>
        </div>
    );
}

export default App;