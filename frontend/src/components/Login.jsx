import { useState } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import { motion } from 'framer-motion';
import { Mail, Lock, ArrowRight, Loader2 } from 'lucide-react';

export default function Login({ setIsAuthenticated }) {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);
    const navigate = useNavigate();

    const handleLogin = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError('');

        try {
            const response = await axios.post('/login.php', { email, password });

            if (response.data.success) {
                setIsAuthenticated(true);
                // Success animation delay
                setTimeout(() => {
                    navigate('/');
                }, 800);
            } else {
                setError(response.data.message || 'Identifiants incorrects');
                setLoading(false);
            }
        } catch (err) {
            setError('Erreur de connexion au serveur');
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen flex w-full bg-slate-900 overflow-hidden relative">

            {/* Background Image with Overlay */}
            <div className="absolute inset-0 z-0">
                <div className="absolute inset-0 bg-gradient-to-r from-slate-900 via-slate-900/90 to-slate-900/40 z-10" />
                <motion.div
                    initial={{ scale: 1.1 }}
                    animate={{ scale: 1 }}
                    transition={{ duration: 10, repeat: Infinity, repeatType: "reverse" }}
                    className="w-full h-full bg-cover bg-center"
                    style={{ backgroundImage: "url('https://images.unsplash.com/photo-1539020140153-e479b8c22e70?q=80&w=2070&auto=format&fit=crop')" }} // Luxury Desert/Morocco Vibe
                />
            </div>

            <div className="relative z-20 flex w-full h-screen">

                {/* Left Side - Brand Story */}
                <div className="hidden lg:flex w-1/2 flex-col justify-center px-20 relative">
                    <motion.div
                        initial={{ opacity: 0, y: 30 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8, delay: 0.2 }}
                        className="max-w-xl"
                    >
                        <div className="flex items-center gap-4 mb-8">
                            <div className="h-px w-12 bg-amber-400/60"></div>
                            <span className="text-amber-400 uppercase tracking-[0.2em] text-sm font-semibold">Panel Administrateur</span>
                        </div>

                        <h1 className="text-6xl font-bold font-serif text-white mb-6 leading-tight">
                            L'Excellence <br />
                            <span className="text-transparent bg-clip-text bg-gradient-to-r from-amber-200 to-amber-500">
                                Sans Compromis.
                            </span>
                        </h1>

                        <p className="text-slate-300 text-lg leading-relaxed mb-10 border-l-2 border-slate-700 pl-6">
                            Gérez vos voyages, factures et clients avec la précision d'un orfèvre.
                            Bienvenue dans votre espace de gestion Ameziane Tours.
                        </p>
                    </motion.div>
                </div>

                {/* Right Side - Login Form */}
                <div className="w-full lg:w-1/2 flex items-center justify-center p-8">
                    <motion.div
                        initial={{ opacity: 0, x: 50 }}
                        animate={{ opacity: 1, x: 0 }}
                        transition={{ duration: 0.8 }}
                        className="w-full max-w-[450px]"
                    >
                        {/* Glassmorphism Card */}
                        <div className="bg-white/5 backdrop-blur-xl border border-white/10 p-10 rounded-3xl shadow-2xl relative overflow-hidden group">

                            {/* Decorative Glow */}
                            <div className="absolute -top-10 -right-10 w-40 h-40 bg-amber-500/10 rounded-full blur-3xl pointer-events-none group-hover:bg-amber-500/20 transition-all duration-700"></div>

                            {/* Logo Section */}
                            <div className="flex flex-col items-center mb-10">
                                <motion.div
                                    initial={{ scale: 0.8, opacity: 0 }}
                                    animate={{ scale: 1, opacity: 1 }}
                                    transition={{ delay: 0.4 }}
                                    className="relative mb-6"
                                >
                                    <div className="absolute inset-0 bg-amber-400/20 blur-xl rounded-full"></div>
                                    <img
                                        src="/eagle-logo.png"
                                        alt="Ameziane Tours Eagle"
                                        className="h-24 w-auto drop-shadow-lg relative z-10 filter drop-shadow-[0_0_15px_rgba(251,191,36,0.3)]"
                                    />
                                </motion.div>
                                <h2 className="text-2xl font-serif text-white tracking-wide">AMEZIANE TOURS</h2>
                                <p className="text-slate-400 text-sm tracking-widest uppercase mt-1">Prestigio</p>
                            </div>

                            <form onSubmit={handleLogin} className="space-y-6">
                                <div className="space-y-2">
                                    <label className="text-xs text-slate-300 font-medium uppercase tracking-wider ml-1">Email Professionnel</label>
                                    <div className="relative group">
                                        <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-amber-400 text-slate-500">
                                            <Mail className="h-5 w-5" />
                                        </div>
                                        <input
                                            type="email"
                                            value={email}
                                            onChange={(e) => setEmail(e.target.value)}
                                            className="block w-full pl-11 pr-4 py-4 bg-slate-900/50 border border-slate-700 rounded-xl text-slate-200 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all cursor-text hover:bg-slate-900/70"
                                            placeholder="admin@ameziane-tours.com"
                                            required
                                        />
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <label className="text-xs text-slate-300 font-medium uppercase tracking-wider ml-1">Mot de passe</label>
                                    <div className="relative group">
                                        <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-amber-400 text-slate-500">
                                            <Lock className="h-5 w-5" />
                                        </div>
                                        <input
                                            type="password"
                                            value={password}
                                            onChange={(e) => setPassword(e.target.value)}
                                            className="block w-full pl-11 pr-4 py-4 bg-slate-900/50 border border-slate-700 rounded-xl text-slate-200 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500/50 transition-all cursor-text hover:bg-slate-900/70"
                                            placeholder="••••••••"
                                            required
                                        />
                                    </div>
                                </div>

                                {error && (
                                    <motion.div
                                        initial={{ opacity: 0, y: -10 }}
                                        animate={{ opacity: 1, y: 0 }}
                                        className="p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm flex items-center gap-2 justify-center"
                                    >
                                        <span>{error}</span>
                                    </motion.div>
                                )}

                                <button
                                    type="submit"
                                    disabled={loading}
                                    className="w-full flex items-center justify-center py-4 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-slate-900 bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-amber-500 transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98] disabled:opacity-70 disabled:cursor-not-allowed mt-4"
                                >
                                    {loading ? (
                                        <Loader2 className="w-5 h-5 animate-spin" />
                                    ) : (
                                        <span className="flex items-center gap-2 uppercase tracking-wider">
                                            Connexion Espace Admin
                                            <ArrowRight className="w-5 h-5" />
                                        </span>
                                    )}
                                </button>
                            </form>
                        </div>

                        <div className="text-center mt-8">
                            <p className="text-slate-500 text-xs">
                                &copy; {new Date().getFullYear()} Ameziane Tours &bull; Système Sécurisé v2.0
                            </p>
                        </div>
                    </motion.div>
                </div>
            </div>
        </div>
    );
}
