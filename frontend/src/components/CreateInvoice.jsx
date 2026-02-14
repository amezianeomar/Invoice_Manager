import { useState, useEffect } from 'react';
import { useForm, useFieldArray } from 'react-hook-form';
import axios from 'axios';
import { motion, AnimatePresence } from 'framer-motion';
import { X, Plus, Trash2, Save, User, Calendar, MapPin, FileText, CheckCircle } from 'lucide-react';

export default function CreateInvoice({ onCancel }) {
    const { register, control, handleSubmit, watch, setValue, formState: { errors } } = useForm({
        defaultValues: {
            services: [{ service_id: '', quantity: 1, unit_price: 0, from_location: '', to_location: '', city: '', custom_desc: '' }]
        }
    });

    const { fields, append, remove } = useFieldArray({
        control,
        name: "services"
    });

    const [clients, setClients] = useState([]);
    const [servicesList, setServicesList] = useState([]);
    const [isNewClient, setIsNewClient] = useState(false);
    const [newClientName, setNewClientName] = useState('');
    const [loading, setLoading] = useState(false);
    const [success, setSuccess] = useState(false);

    // Watch for calculations
    const watchServices = watch("services");
    const [total, setTotal] = useState(0);

    useEffect(() => {
        const fetchResources = async () => {
            try {
                const [clientsRes, servicesRes] = await Promise.all([
                    axios.get('/resources.php?type=clients'),
                    axios.get('/resources.php?type=services')
                ]);
                setClients(Array.isArray(clientsRes.data.data) ? clientsRes.data.data : []);
                setServicesList(Array.isArray(servicesRes.data.data) ? servicesRes.data.data : []);
            } catch (error) {
                console.error("Error loading resources", error);
            }
        };
        fetchResources();
    }, []);

    useEffect(() => {
        const newTotal = watchServices.reduce((sum, service) => {
            return sum + (parseFloat(service.unit_price) || 0);
        }, 0);
        setTotal(newTotal);
    }, [watchServices]);

    const onSubmit = async (data) => {
        setLoading(true);
        try {
            let finalClientId = data.client_id;

            if (isNewClient && newClientName) {
                const clientRes = await axios.post('/resources.php', {
                    name: newClientName,
                    type: 'client'
                });
                if (clientRes.data.success) {
                    finalClientId = clientRes.data.id;
                }
            }

            const invoiceData = {
                ...data,
                client_id: finalClientId,
                custom_total: total
            };

            const response = await axios.post('/invoices.php', invoiceData);
            if (response.data.success) {
                setSuccess(true);
                setTimeout(() => {
                    onCancel();
                }, 1500);
            }
        } catch (error) {
            alert("Erreur lors de la création");
            console.error(error);
        } finally {
            setLoading(false);
        }
    };

    const handleServiceChange = (index, serviceId) => {
        const selectedService = servicesList.find(s => s.id == serviceId);
        if (selectedService) {
            setValue(`services.${index}.unit_price`, selectedService.default_price);
        }
    };

    if (success) {
        return (
            <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 backdrop-blur-sm">
                <motion.div
                    initial={{ scale: 0.8, opacity: 0 }}
                    animate={{ scale: 1, opacity: 1 }}
                    className="bg-white p-8 rounded-2xl shadow-2xl flex flex-col items-center"
                >
                    <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4 text-green-600">
                        <CheckCircle className="w-8 h-8" />
                    </div>
                    <h3 className="text-xl font-bold text-slate-900 font-serif">Facture Créée !</h3>
                    <p className="text-slate-500 mt-2">Redirection vers le tableau de bord...</p>
                </motion.div>
            </div>
        );
    }

    return (
        <AnimatePresence>
            <motion.div
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                exit={{ opacity: 0 }}
                className="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm"
                onClick={onCancel}
            />

            <motion.div
                initial={{ y: "100%" }}
                animate={{ y: 0 }}
                exit={{ y: "100%" }}
                transition={{ type: "spring", damping: 25, stiffness: 300 }}
                className="fixed inset-x-0 bottom-0 z-50 bg-slate-50 rounded-t-3xl shadow-2xl h-[92vh] md:h-[85vh] flex flex-col md:max-w-4xl md:mx-auto md:relative md:top-[10vh] md:rounded-2xl"
            >
                {/* Header */}
                <div className="flex items-center justify-between p-6 border-b border-slate-200 bg-white rounded-t-3xl md:rounded-t-2xl sticky top-0 z-10">
                    <div>
                        <h2 className="text-2xl font-bold font-serif text-slate-800 flex items-center gap-2">
                            <Plus className="w-6 h-6 text-amber-500" />
                            Nouvelle Facture
                        </h2>
                        <p className="text-xs text-slate-500 mt-1">Remplissez les informations ci-dessous</p>
                    </div>
                    <button onClick={onCancel} className="p-2 bg-slate-100 hover:bg-slate-200 rounded-full transition-colors text-slate-600">
                        <X className="w-6 h-6" />
                    </button>
                </div>

                {/* Form Content */}
                <div className="flex-1 overflow-y-auto p-6 space-y-8">
                    <form id="invoice-form" onSubmit={handleSubmit(onSubmit)} className="space-y-8">

                        {/* Client Section */}
                        <div className="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                            <div className="flex items-center gap-2 mb-4 text-slate-800 font-semibold border-b border-slate-50 pb-2">
                                <User className="w-5 h-5 text-blue-600" />
                                <h3>Informations Client</h3>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-sm font-medium text-slate-700 mb-2">Choisir un Client</label>
                                    <select
                                        {...register("client_id")}
                                        disabled={isNewClient}
                                        className="w-full p-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none transition-all disabled:opacity-50"
                                    >
                                        <option value="">Sélectionner...</option>
                                        {clients.map(c => (
                                            <option key={c.id} value={c.id}>{c.name}</option>
                                        ))}
                                    </select>
                                </div>

                                <div className="flex items-center gap-3 pt-6">
                                    <input
                                        type="checkbox"
                                        id="newClient"
                                        checked={isNewClient}
                                        onChange={(e) => setIsNewClient(e.target.checked)}
                                        className="w-5 h-5 text-blue-600 rounded focus:ring-blue-500 border-gray-300"
                                    />
                                    <label htmlFor="newClient" className="text-sm font-medium text-slate-700 cursor-pointer select-none">
                                        Ou ajouter un nouveau client
                                    </label>
                                </div>

                                {isNewClient && (
                                    <div className="md:col-span-2">
                                        <label className="block text-sm font-medium text-slate-700 mb-2">Nom du Nouveau Client</label>
                                        <input
                                            type="text"
                                            value={newClientName}
                                            onChange={(e) => setNewClientName(e.target.value)}
                                            className="w-full p-3 rounded-xl border border-blue-200 bg-blue-50/30 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none transition-all"
                                            placeholder="Ex: Agence Voyage XYZ..."
                                        />
                                    </div>
                                )}

                                <div className="md:col-span-2">
                                    <label className="block text-sm font-medium text-slate-700 mb-2">Date de Facturation</label>
                                    <div className="relative">
                                        <Calendar className="absolute left-3 top-3 w-5 h-5 text-slate-400" />
                                        <input
                                            type="date"
                                            {...register("invoice_date", { required: true })}
                                            className="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 outline-none transition-all"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Services Section */}
                        <div className="space-y-4">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-2 text-slate-800 font-semibold">
                                    <FileText className="w-5 h-5 text-blue-600" />
                                    <h3>Prestations</h3>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => append({ service_id: '', quantity: 1, unit_price: 0 })}
                                    className="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1 bg-blue-50 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition-colors"
                                >
                                    <Plus className="w-4 h-4" />
                                    Ajouter
                                </button>
                            </div>

                            <div className="space-y-4">
                                {fields.map((field, index) => {
                                    const serviceType = watch(`services.${index}.service_id`);
                                    return (
                                        <motion.div
                                            key={field.id}
                                            initial={{ opacity: 0, y: 10 }}
                                            animate={{ opacity: 1, y: 0 }}
                                            className="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 group relative"
                                        >
                                            <button
                                                type="button"
                                                onClick={() => remove(index)}
                                                className="absolute top-4 right-4 p-2 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all"
                                            >
                                                <Trash2 className="w-4 h-4" />
                                            </button>

                                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 pr-8 mb-4">
                                                <div className="lg:col-span-2">
                                                    <label className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1 block">Service</label>
                                                    <select
                                                        {...register(`services.${index}.service_id`, { required: true })}
                                                        onChange={(e) => handleServiceChange(index, e.target.value)}
                                                        className="w-full p-2.5 rounded-lg border border-slate-200 bg-slate-50 text-sm focus:ring-2 focus:ring-blue-500/20 outline-none"
                                                    >
                                                        <option value="">Choisir...</option>
                                                        {servicesList.map(s => (
                                                            <option key={s.id} value={s.id}>{s.title}</option>
                                                        ))}
                                                    </select>
                                                </div>

                                                <div>
                                                    <label className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1 block">Prix (MAD)</label>
                                                    <input
                                                        type="number"
                                                        {...register(`services.${index}.unit_price`, { required: true })}
                                                        className="w-full p-2.5 rounded-lg border border-slate-200 bg-slate-50 text-sm focus:ring-2 focus:ring-blue-500/20 outline-none font-mono"
                                                    />
                                                </div>

                                                <div>
                                                    <label className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1 block">Date</label>
                                                    <input
                                                        type="date"
                                                        {...register(`services.${index}.service_date`)}
                                                        className="w-full p-2.5 rounded-lg border border-slate-200 bg-slate-50 text-sm focus:ring-2 focus:ring-blue-500/20 outline-none"
                                                    />
                                                </div>
                                            </div>

                                            {/* Dynamic Fields */}
                                            <div className="bg-slate-50/50 p-4 rounded-xl border border-dashed border-slate-200">
                                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    {serviceType === '1' && (
                                                        <>
                                                            <div className="relative">
                                                                <MapPin className="absolute left-3 top-3 w-4 h-4 text-slate-400" />
                                                                <input
                                                                    {...register(`services.${index}.from_location`)}
                                                                    placeholder="De (Ville départ)"
                                                                    className="w-full pl-10 px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-500"
                                                                />
                                                            </div>
                                                            <div className="relative">
                                                                <MapPin className="absolute left-3 top-3 w-4 h-4 text-slate-400" />
                                                                <input
                                                                    {...register(`services.${index}.to_location`)}
                                                                    placeholder="À (Ville arrivée)"
                                                                    className="w-full pl-10 px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-500"
                                                                />
                                                            </div>
                                                        </>
                                                    )}

                                                    {(serviceType === '2' || serviceType === '3') && (
                                                        <div className="col-span-2">
                                                            <div className="relative">
                                                                <MapPin className="absolute left-3 top-3 w-4 h-4 text-slate-400" />
                                                                <input
                                                                    {...register(`services.${index}.city`)}
                                                                    placeholder="Ville concernée"
                                                                    className="w-full pl-10 px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-500"
                                                                />
                                                            </div>
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                        </motion.div>
                                    );
                                })}
                            </div>
                        </div>
                    </form>
                </div>

                {/* Footer */}
                <div className="p-6 border-t border-slate-200 bg-slate-50 rounded-b-2xl sticky bottom-0 z-10 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div className="text-center md:text-left">
                        <span className="text-slate-500 text-sm font-medium uppercase tracking-wider">Total Estimé</span>
                        <div className="text-3xl font-bold text-slate-900 font-serif">
                            {new Intl.NumberFormat('fr-MA', { style: 'currency', currency: 'MAD' }).format(total)}
                        </div>
                    </div>

                    <div className="flex gap-3 w-full md:w-auto">
                        <button
                            onClick={onCancel}
                            className="flex-1 md:flex-none px-6 py-3.5 rounded-xl border border-slate-300 text-slate-600 font-medium hover:bg-slate-100 transition-colors"
                        >
                            Annuler
                        </button>
                        <button
                            form="invoice-form"
                            type="submit"
                            disabled={loading}
                            className="flex-1 md:flex-none bg-slate-900 hover:bg-slate-800 text-white px-8 py-3.5 rounded-xl shadow-lg shadow-slate-900/20 flex items-center justify-center gap-2 transition-all font-medium active:scale-[0.98] disabled:opacity-70"
                        >
                            {loading ? <div className="animate-spin w-5 h-5 border-2 border-white/30 border-t-white rounded-full"></div> : <Save className="w-5 h-5 text-amber-400" />}
                            Enregistrer
                        </button>
                    </div>
                </div>
            </motion.div>
        </AnimatePresence>
    );
}
