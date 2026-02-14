import { useState, useEffect } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';
import {
    Plus, Search, LogOut, FileText, Trash2, Edit2,
    TrendingUp, Calendar, Users, ChevronDown, Download
} from 'lucide-react';
import CreateInvoice from './CreateInvoice';
import EditInvoice from './EditInvoice';

export default function Dashboard({ setIsAuthenticated }) {
    const [invoices, setInvoices] = useState([]);
    const [loading, setLoading] = useState(true);
    const [showCreate, setShowCreate] = useState(false);
    const [editingInvoiceId, setEditingInvoiceId] = useState(null);
    const [searchTerm, setSearchTerm] = useState('');
    const navigate = useNavigate();

    useEffect(() => {
        fetchInvoices();
    }, []);

    const fetchInvoices = async () => {
        try {
            const response = await axios.get('/invoices.php');
            if (response.data.success) {
                setInvoices(response.data.data);
            }
        } catch (error) {
            console.error("Error fetching invoices", error);
        } finally {
            setLoading(false);
        }
    };

    const handleLogout = async () => {
        await axios.post('/logout.php');
        setIsAuthenticated(false);
        navigate('/login');
    };

    const handleDelete = async (id) => {
        if (!window.confirm("Voulez-vous vraiment supprimer cette facture ?")) return;
        try {
            await axios.delete(`/invoices.php?id=${id}`);
            fetchInvoices();
        } catch (error) {
            alert("Erreur lors de la suppression");
        }
    };

    const filteredInvoices = invoices.filter(inv =>
        inv.client_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        inv.id.toString().includes(searchTerm)
    );

    // Calculate Summary Stats
    const totalRevenue = invoices.reduce((sum, inv) => sum + parseFloat(inv.total), 0);
    const monthlyRevenue = invoices
        .filter(inv => new Date(inv.invoice_date).getMonth() === new Date().getMonth())
        .reduce((sum, inv) => sum + parseFloat(inv.total), 0);

    const formatCurrency = (amount) =>
        new Intl.NumberFormat('fr-MA', { style: 'currency', currency: 'MAD' }).format(amount);

    const formatDate = (dateString) =>
        new Date(dateString).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });

    if (showCreate) {
        return <CreateInvoice onCancel={() => { setShowCreate(false); fetchInvoices(); }} />;
    }

    if (editingInvoiceId) {
        return <EditInvoice invoiceId={editingInvoiceId} onCancel={() => { setEditingInvoiceId(null); fetchInvoices(); }} />;
    }

    // Animation Variants
    const containerVariants = {
        hidden: { opacity: 0 },
        visible: {
            opacity: 1,
            transition: { staggerChildren: 0.1 }
        }
    };

    const itemVariants = {
        hidden: { y: 20, opacity: 0 },
        visible: { y: 0, opacity: 1 }
    };

    return (
        <div className="min-h-screen bg-slate-50 relative pb-20">
            {/* Background Decoration */}
            <div className="fixed inset-0 z-0 pointer-events-none">
                <div className="absolute top-0 left-0 w-full h-[300px] bg-gradient-to-b from-blue-900 to-slate-50 opacity-10"></div>
            </div>

            {/* Navbar */}
            <nav className="sticky top-0 z-40 w-full glass border-b border-white/20">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16 items-center">
                        <div className="flex items-center gap-3">
                            <div className="bg-slate-900 text-amber-400 p-2 rounded-xl shadow-lg shadow-blue-900/10">
                                <FileText className="w-6 h-6" />
                            </div>
                            <div>
                                <h1 className="font-serif font-bold text-lg text-slate-900 tracking-tight leading-none">AMEZIANE TOURS</h1>
                                <span className="text-[9px] uppercase tracking-[0.2em] text-slate-500 font-bold">Prestigio</span>
                            </div>
                        </div>
                        <button onClick={handleLogout} className="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all">
                            <LogOut className="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </nav>

            <main className="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {/* Header Section */}
                <div className="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
                    <motion.div initial={{ x: -20, opacity: 0 }} animate={{ x: 0, opacity: 1 }}>
                        <h2 className="text-3xl font-bold text-slate-900 font-serif">Tableau de Bord</h2>
                        <p className="text-slate-500 mt-1 text-sm font-medium">Aperçu de vos activités récentes</p>
                    </motion.div>
                    <motion.button
                        initial={{ x: 20, opacity: 0 }}
                        animate={{ x: 0, opacity: 1 }}
                        whileHover={{ scale: 1.02 }}
                        whileTap={{ scale: 0.98 }}
                        onClick={() => setShowCreate(true)}
                        className="w-full md:w-auto bg-slate-900 hover:bg-slate-800 text-white px-6 py-3.5 rounded-xl shadow-lg shadow-slate-900/20 flex items-center justify-center gap-2 transition-all font-medium"
                    >
                        <Plus className="w-5 h-5 text-amber-400" />
                        Nouvelle Facture
                    </motion.button>
                </div>

                {/* KPI Cards */}
                <motion.div
                    initial={{ y: 20, opacity: 0 }}
                    animate={{ y: 0, opacity: 1 }}
                    transition={{ delay: 0.2 }}
                    className="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 mb-8"
                >
                    <div className="glass-card p-6 border-l-4 border-slate-900 flex items-center justify-between">
                        <div>
                            <div className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Chiffre d'Affaires</div>
                            <div className="text-2xl font-bold text-slate-900 font-serif">{formatCurrency(totalRevenue)}</div>
                        </div>
                        <div className="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600">
                            <TrendingUp className="w-5 h-5" />
                        </div>
                    </div>
                    <div className="glass-card p-6 border-l-4 border-amber-400 flex items-center justify-between">
                        <div>
                            <div className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Revenu Ce Mois</div>
                            <div className="text-2xl font-bold text-slate-900 font-serif">{formatCurrency(monthlyRevenue)}</div>
                        </div>
                        <div className="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center text-amber-500">
                            <Calendar className="w-5 h-5" />
                        </div>
                    </div>
                    <div className="glass-card p-6 border-l-4 border-blue-500 flex items-center justify-between">
                        <div>
                            <div className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Factures</div>
                            <div className="text-2xl font-bold text-slate-900 font-serif">{invoices.length}</div>
                        </div>
                        <div className="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-500">
                            <Users className="w-5 h-5" />
                        </div>
                    </div>
                </motion.div>

                {/* Search */}
                <motion.div
                    initial={{ y: 20, opacity: 0 }}
                    animate={{ y: 0, opacity: 1 }}
                    transition={{ delay: 0.3 }}
                    className="glass-card p-2 mb-6 flex items-center gap-4 sticky top-20 z-30"
                >
                    <div className="relative flex-1">
                        <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                        <input
                            type="text"
                            placeholder="Rechercher..."
                            className="w-full pl-12 pr-4 py-3 rounded-xl border-none focus:ring-2 focus:ring-blue-500/20 outline-none transition-all text-sm bg-transparent font-medium"
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                        />
                    </div>
                </motion.div>

                {/* Content */}
                {loading ? (
                    <div className="py-20 text-center flex flex-col items-center justify-center">
                        <div className="animate-spin text-slate-300 mb-4">
                            <FileText className="w-8 h-8" />
                        </div>
                        <p className="text-slate-400 font-medium">Chargement des données...</p>
                    </div>
                ) : filteredInvoices.length === 0 ? (
                    <div className="glass-card p-12 text-center text-slate-400">
                        Aucune facture trouvée.
                    </div>
                ) : (
                    <motion.div
                        variants={containerVariants}
                        initial="hidden"
                        animate="visible"
                        className="space-y-4"
                    >
                        {/* Desktop Table Header - Hidden on Mobile */}
                        <div className="hidden md:grid grid-cols-12 gap-4 px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <div className="col-span-1">ID</div>
                            <div className="col-span-4">Client</div>
                            <div className="col-span-3">Date</div>
                            <div className="col-span-2 text-right">Montant</div>
                            <div className="col-span-2 text-right">Actions</div>
                        </div>

                        {filteredInvoices.map((invoice) => (
                            <motion.div
                                key={invoice.id}
                                variants={itemVariants}
                                whileHover={{ scale: 1.005 }}
                                className="glass-card p-5 md:py-4 md:px-6 group relative overflow-hidden"
                            >
                                <div className="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                                    {/* Mobile: Header Row with ID and Actions */}
                                    <div className="flex md:hidden justify-between items-center mb-2 border-b border-slate-50 pb-2">
                                        <span className="font-mono text-xs font-bold text-slate-400">#{invoice.id}</span>
                                        <div className="flex gap-1">
                                            <a
                                                href={`${axios.defaults.baseURL.replace('/api', '')}/generate-pdf.php?id=${invoice.id}`}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg"
                                            >
                                                <Download className="w-4 h-4" />
                                            </a>
                                            <button onClick={() => setEditingInvoiceId(invoice.id)} className="p-2 text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg">
                                                <Edit2 className="w-4 h-4" />
                                            </button>
                                            <button onClick={() => handleDelete(invoice.id)} className="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg">
                                                <Trash2 className="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>

                                    {/* ID (Desktop) */}
                                    <div className="hidden md:block col-span-1 font-mono text-xs font-bold text-slate-400">
                                        #{invoice.id}
                                    </div>

                                    {/* Client Info */}
                                    <div className="col-span-1 md:col-span-4 flex items-center gap-3">
                                        <div className="w-10 h-10 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold border border-slate-200">
                                            {invoice.client_name.substring(0, 2).toUpperCase()}
                                        </div>
                                        <div>
                                            <div className="font-bold text-slate-800 text-sm md:text-base">{invoice.client_name}</div>
                                            <div className="text-xs text-slate-400 md:hidden">{formatDate(invoice.invoice_date)}</div>
                                        </div>
                                    </div>

                                    {/* Date (Desktop) */}
                                    <div className="hidden md:block col-span-3 text-sm font-medium text-slate-600">
                                        {formatDate(invoice.invoice_date)}
                                    </div>

                                    {/* Amount */}
                                    <div className="col-span-1 md:col-span-2 flex justify-between md:justify-end items-center">
                                        <span className="md:hidden text-xs font-bold text-slate-400 uppercase">Total</span>
                                        <span className="font-serif font-bold text-slate-900 text-lg">
                                            {formatCurrency(invoice.total)}
                                        </span>
                                    </div>

                                    {/* Actions (Desktop) */}
                                    <div className="hidden md:flex col-span-2 justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a
                                            href={`${axios.defaults.baseURL.replace('/api', '')}/generate-pdf.php?id=${invoice.id}`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                            title="PDF"
                                        >
                                            <Download className="w-4 h-4" />
                                        </a>
                                        <button
                                            onClick={() => setEditingInvoiceId(invoice.id)}
                                            className="p-2 text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-colors"
                                            title="Modifier"
                                        >
                                            <Edit2 className="w-4 h-4" />
                                        </button>
                                        <button
                                            onClick={() => handleDelete(invoice.id)}
                                            className="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                            title="Supprimer"
                                        >
                                            <Trash2 className="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </motion.div>
                        ))}
                    </motion.div>
                )}
            </main>
        </div>
    );
}
