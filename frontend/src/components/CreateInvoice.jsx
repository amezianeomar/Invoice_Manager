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
    const [isManualTotal, setIsManualTotal] = useState(false);

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

    // Derived state for total - much more reliable than useEffect
    const calculatedTotal = watchServices ? watchServices.reduce((sum, service) => {
        return sum + ((parseFloat(service.unit_price) || 0) * (parseFloat(service.quantity) || 1));
    }, 0) : 0;

    const handleServiceChange = (index, serviceId) => {
        const selectedService = servicesList.find(s => s.id == serviceId);
        if (selectedService) {
            setValue(`services.${index}.unit_price`, selectedService.default_price);
        }
    };

    const handleSmartFill = (index, field, value) => {
        const service = watchServices[index];
        const serviceId = service.service_id;
        let newDesc = service.custom_desc;

        if (serviceId === '1') { // Transport
            const from = field === 'from' ? value : service.from_location;
            const to = field === 'to' ? value : service.to_location;
            if (from && to) newDesc = `Transfert ${from} -> ${to}`;
        } else if (serviceId === '3') { // Excursion
            const city = field === 'city' ? value : service.city;
            if (city) newDesc = `Excursion ${city}`;
        } else if (serviceId === '2') { // Mise a disposition
            const city = field === 'city' ? value : service.city;
            if (city) newDesc = `Mise a disposition (${city})`;
        }

        if (newDesc) {
            setValue(`services.${index}.custom_desc`, newDesc);
        }
    };

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
                custom_total: isManualTotal ? data.manual_total : calculatedTotal,
                total: isManualTotal ? data.manual_total : calculatedTotal
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

                                {/* Manual Total Toggle */}
                                <div className="md:col-span-2 border-t border-slate-50 pt-4 mt-2">
                                    <div className="flex flex-col md:flex-row justify-between items-center gap-4">
                                        <div className="flex items-center gap-3">
                                            <div className="relative inline-block w-12 h-6 transition-colors duration-200 ease-in-out border-2 border-transparent rounded-full cursor-pointer bg-slate-200">
                                                <input
                                                    type="checkbox"
                                                    id="manualTotal"
                                                    checked={isManualTotal}
                                                    onChange={(e) => {
                                                        setIsManualTotal(e.target.checked);
                                                        if (!e.target.checked) setValue('manual_total', null);
                                                    }}
                                                    className="absolute w-0 h-0 opacity-0"
                                                />
                                                <label htmlFor="manualTotal" className={`block overflow-hidden h-6 rounded-full cursor-pointer transition-colors ${isManualTotal ? 'bg-blue-600' : 'bg-slate-300'}`}></label>
                                                <div className={`absolute left-0 inline-block w-6 h-6 mb-1 transition-transform duration-200 ease-in-out transform bg-white rounded-full shadow-md ${isManualTotal ? 'translate-x-6' : 'translate-x-0'}`}></div>
                                            </div>
                                            <label htmlFor="manualTotal" className="text-sm font-semibold text-slate-600 cursor-pointer select-none">
                                                Saisir le montant total manuellement
                                            </label>
                                        </div>

                                        <div className="flex items-center gap-4">
                                            <span className="text-sm text-slate-500 font-medium uppercase tracking-wide">Total Facture:</span>
                                            {isManualTotal ? (
                                                <div className="relative w-48">
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        {...register("manual_total")}
                                                        className="w-full px-4 py-2 text-right text-xl font-bold text-blue-700 bg-blue-50 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500/20 outline-none"
                                                        placeholder={calculatedTotal.toFixed(2)}
                                                    />
                                                    <span className="absolute right-12 top-1/2 -translate-y-1/2 text-xs text-blue-400 font-bold pointer-events-none">MAD</span>
                                                </div>
                                            ) : (
                                                <div className="text-2xl font-bold text-slate-800">
                                                    {calculatedTotal.toFixed(2)} <span className="text-sm text-slate-400 font-medium">MAD</span>
                                                </div>
                                            )}
                                        </div>
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
                                                className="absolute top-4 right-4 p-2 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all z-10"
                                            >
                                                <Trash2 className="w-4 h-4" />
                                            </button>

                                            {/* Row 1: Service & Price */}
                                            <div className="grid grid-cols-1 md:grid-cols-12 gap-6 mb-4">
                                                <div className="md:col-span-6">
                                                    <label className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1 block">Type de Service</label>
                                                    <select
                                                        {...register(`services.${index}.service_id`, { required: true })}
                                                        onChange={(e) => handleServiceChange(index, e.target.value)}
                                                        className="w-full p-3 rounded-lg border border-slate-200 bg-slate-50 text-sm focus:ring-2 focus:ring-blue-500/20 outline-none transition-all font-medium text-slate-700"
                                                    >
                                                        <option value="">Sélectionner...</option>
                                                        {servicesList.map(s => (
                                                            <option key={s.id} value={s.id}>{s.title}</option>
                                                        ))}
                                                    </select>
                                                </div>

                                                <div className="md:col-span-2">
                                                    <label className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1 block">Qté</label>
                                                    <input
                                                        type="number"
                                                        {...register(`services.${index}.quantity`, { required: true, min: 1 })}
                                                        className="w-full p-3 rounded-lg border border-slate-200 bg-white text-sm focus:ring-2 focus:ring-blue-500/20 outline-none font-bold text-slate-700 text-center"
                                                        placeholder="1"
                                                    />
                                                </div>

                                                <div className="md:col-span-4">
                                                    <label className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1 block">Prix Unitaire (MAD)</label>
                                                    <div className="relative">
                                                        <input
                                                            type="number"
                                                            {...register(`services.${index}.unit_price`, { required: true })}
                                                            className="w-full pl-3 pr-10 py-3 rounded-lg border border-slate-200 bg-white text-sm focus:ring-2 focus:ring-blue-500/20 outline-none font-mono font-bold text-slate-700 text-right"
                                                            placeholder="0.00"
                                                        />
                                                        <span className="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none">MAD</span>
                                                    </div>
                                                </div>
                                            </div>

                                            {/* Row 2: Dynamic Fields & Description */}
                                            <div className="bg-slate-50/50 p-4 rounded-xl border border-dashed border-slate-200 space-y-4 mb-4">
                                                {/* Dynamic Location/City Fields */}
                                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    {serviceType === '1' && (
                                                        <>
                                                            <div className="relative">
                                                                <MapPin className="absolute left-3 top-3 w-4 h-4 text-slate-400" />
                                                                <input
                                                                    {...register(`services.${index}.from_location`)}
                                                                    onChange={(e) => {
                                                                        register(`services.${index}.from_location`).onChange(e);
                                                                        handleSmartFill(index, 'from', e.target.value);
                                                                    }}
                                                                    placeholder="De (Ville départ)"
                                                                    className="w-full pl-10 px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-500"
                                                                />
                                                            </div>
                                                            <div className="relative">
                                                                <MapPin className="absolute left-3 top-3 w-4 h-4 text-slate-400" />
                                                                <input
                                                                    {...register(`services.${index}.to_location`)}
                                                                    onChange={(e) => {
                                                                        register(`services.${index}.to_location`).onChange(e);
                                                                        handleSmartFill(index, 'to', e.target.value);
                                                                    }}
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
                                                                    onChange={(e) => {
                                                                        register(`services.${index}.city`).onChange(e);
                                                                        handleSmartFill(index, 'city', e.target.value);
                                                                    }}
                                                                    placeholder="Ville concernée"
                                                                    className="w-full pl-10 px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm outline-none focus:border-blue-500"
                                                                />
                                                            </div>
                                                        </div>
                                                    )}
                                                </div>

                                                {/* Description Field (RESTORED) */}
                                                <div>
                                                    <label className="text-[10px] font-bold text-blue-500 uppercase tracking-widest mb-1 block flex items-center gap-1">
                                                        <FileText className="w-3 h-3" />
                                                        Description sur Facture
                                                    </label>
                                                    <textarea
                                                        {...register(`services.${index}.custom_desc`)}
                                                        rows="2"
                                                        className="w-full p-3 rounded-lg border border-blue-100 bg-blue-50/20 text-sm focus:ring-2 focus:ring-blue-500/20 outline-none transition-all placeholder-slate-400"
                                                        placeholder="Description détaillée qui apparaîtra sur le PDF..."
                                                    ></textarea>
                                                </div>
                                            </div>

                                            {/* Row 3: Date */}
                                            <div>
                                                <label className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1 block">Date de la prestation</label>
                                                <div className="relative max-w-xs">
                                                    <Calendar className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                                                    <input
                                                        type="date"
                                                        {...register(`services.${index}.service_date`)}
                                                        className="w-full pl-10 pr-4 py-2 rounded-lg border border-slate-200 bg-slate-50 text-sm focus:ring-2 focus:ring-blue-500/20 outline-none"
                                                    />
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
                            {new Intl.NumberFormat('fr-MA', { style: 'currency', currency: 'MAD' }).format(calculatedTotal)}
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
