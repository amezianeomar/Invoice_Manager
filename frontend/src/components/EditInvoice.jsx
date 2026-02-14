import { useState, useEffect } from 'react';
import axios from 'axios';
import { useFieldArray, useForm } from 'react-hook-form';

export default function EditInvoice({ invoiceId, onCancel }) {
    const [clients, setClients] = useState([]);
    const [servicesList, setServicesList] = useState([]);
    const [showAddClient, setShowAddClient] = useState(false);
    const [newClient, setNewClient] = useState({ name: '', details: '', contact: '' });
    const [creatingClient, setCreatingClient] = useState(false);
    const [isManualTotal, setIsManualTotal] = useState(false);
    const [loading, setLoading] = useState(true);

    const { register, control, handleSubmit, watch, setValue, reset } = useForm({
        defaultValues: {
            invoice_date: new Date().toISOString().split('T')[0],
            manual_total: null,
            services: []
        }
    });

    const { fields, append, remove } = useFieldArray({
        control,
        name: "services"
    });

    const fetchClients = async () => {
        const res = await axios.get('/resources.php?type=clients');
        if (res.data.success) setClients(Array.isArray(res.data.data) ? res.data.data : []);
    };

    useEffect(() => {
        const fetchData = async () => {
            await fetchClients();
            const servicesRes = await axios.get('/resources.php?type=services');
            if (servicesRes.data.success) setServicesList(Array.isArray(servicesRes.data.data) ? servicesRes.data.data : []);

            // Fetch Invoice Details
            try {
                const invoiceRes = await axios.get(`/invoices.php?id=${invoiceId}`);
                if (invoiceRes.data.success) {
                    const invoice = invoiceRes.data.data.invoice;
                    const items = Array.isArray(invoiceRes.data.data.items) ? invoiceRes.data.data.items : [];

                    // Format services for the form
                    const formattedServices = items.map(item => ({
                        service_id: item.service_id,
                        unit_price: item.unit_price,
                        custom_desc: item.custom_desc || "",
                        service_date: item.service_date || "",
                        from_location: item.from_location || "",
                        to_location: item.to_location || "",
                        city: item.city || ""
                    }));

                    // Determine if manual total was used
                    // We sum the items and see if it matches the total
                    const sumItems = formattedServices.reduce((acc, curr) => acc + parseFloat(curr.unit_price || 0), 0);
                    const isManual = Math.abs(parseFloat(invoice.total) - sumItems) > 0.01;

                    setIsManualTotal(isManual);

                    reset({
                        client_id: invoice.client_id,
                        invoice_date: invoice.invoice_date,
                        manual_total: isManual ? invoice.total : null,
                        services: formattedServices
                    });
                }
            } catch (error) {
                alert("Erreur lors du chargement de la facture");
                onCancel();
            } finally {
                setLoading(false);
            }
        };
        fetchData();
    }, [invoiceId]);

    const onSubmit = async (data) => {
        try {
            const finalData = {
                id: invoiceId,
                ...data,
                total: isManualTotal ? data.manual_total : calculatedTotal
            };
            // Use PUT for updates
            const response = await axios.put('/invoices.php', finalData);
            if (response.data.success) {
                alert('Facture mise à jour avec succès !');
                onCancel(); // Return to dashboard
            }
        } catch (error) {
            alert('Erreur: ' + (error.response?.data?.message || error.message));
        }
    };

    const handleCreateClient = async (e) => {
        e.preventDefault();
        if (!newClient.name) return;
        setCreatingClient(true);
        try {
            const res = await axios.post('/resources.php?type=clients', newClient);
            if (res.data.success) {
                await fetchClients();
                setValue('client_id', res.data.id);
                setShowAddClient(false);
                setNewClient({ name: '', details: '', contact: '' });
            }
        } catch (error) {
            alert("Erreur lors de la création du client");
        } finally {
            setCreatingClient(false);
        }
    };

    const watchedServices = watch("services");
    const calculatedTotal = watchedServices ? watchedServices.reduce((sum, service) => sum + (parseFloat(service.unit_price) || 0), 0) : 0;

    const handleSmartFill = (index, field, value) => {
        const service = watchedServices[index];
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

    const { onChange: onClientChange, ...clientRegister } = register("client_id");

    if (loading) {
        return (
            <div className="fixed inset-0 z-50 bg-white flex justify-center items-center">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    return (
        <div className="fixed inset-0 z-50 bg-slate-50 flex flex-col overflow-hidden animate-in slide-in-from-bottom-5 duration-300">
            {/* Add Client Modal */}
            {showAddClient && (
                <div className="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                        <div className="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                            <h3 className="font-bold text-lg text-slate-800">Nouveau Client</h3>
                            <button onClick={() => setShowAddClient(false)} className="text-gray-400 hover:text-gray-600">
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <form onSubmit={handleCreateClient} className="p-6 space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Nom de l'entreprise / Client <span className="text-red-500">*</span></label>
                                <input
                                    autoFocus
                                    type="text"
                                    value={newClient.name}
                                    onChange={e => setNewClient({ ...newClient, name: e.target.value })}
                                    className="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none"
                                    placeholder="Ex: My Morocco"
                                    required
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Détails (ICE, RC, etc.)</label>
                                <input
                                    type="text"
                                    value={newClient.details}
                                    onChange={e => setNewClient({ ...newClient, details: e.target.value })}
                                    className="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none"
                                    placeholder="Ex: ICE: 123456789"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Contact (Email/Tel)</label>
                                <input
                                    type="text"
                                    value={newClient.contact}
                                    onChange={e => setNewClient({ ...newClient, contact: e.target.value })}
                                    className="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none"
                                    placeholder="Optionnel"
                                />
                            </div>
                            <div className="pt-4 flex gap-3">
                                <button type="button" onClick={() => setShowAddClient(false)} className="flex-1 py-2.5 text-slate-600 font-medium hover:bg-slate-50 rounded-xl transition-colors">Annuler</button>
                                <button
                                    type="submit"
                                    disabled={creatingClient}
                                    className="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-xl shadow-lg shadow-blue-600/20 transition-all disabled:opacity-70"
                                >
                                    {creatingClient ? 'Création...' : 'Créer Client'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Header */}
            <div className={`bg-white border-b border-gray-200 p-4 sticky top-0 z-10 shadow-sm transition-all ${showAddClient ? 'blur-sm' : ''}`}>
                <div className="max-w-4xl mx-auto flex justify-between items-center">
                    <div className="flex items-center gap-3">
                        <button onClick={onCancel} className="text-slate-400 hover:text-slate-600 transition-colors">
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        </button>
                        <h2 className="text-xl font-bold text-slate-800 tracking-tight">Modifier Facture #{invoiceId}</h2>
                    </div>
                    <div className="flex gap-2">
                        <button onClick={onCancel} className="text-sm font-medium text-slate-500 hover:text-slate-700 px-4 py-2 rounded-lg hover:bg-slate-100 transition-colors">
                            Annuler
                        </button>
                    </div>
                </div>
            </div>

            {/* Scrollable Content */}
            <div className={`flex-1 overflow-y-auto bg-slate-50 transition-all ${showAddClient ? 'blur-sm pointer-events-none' : ''}`}>
                <div className="max-w-4xl mx-auto w-full p-6 pb-24">
                    <form onSubmit={handleSubmit(onSubmit)} className="space-y-8">

                        {/* Invoice Metadata Card */}
                        <div className="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label className="block text-sm font-semibold text-slate-700 mb-2">Date de Facture</label>
                                <input {...register("invoice_date")} type="date" className="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-slate-700 font-medium" required />
                            </div>
                            <div>
                                <label className="block text-sm font-semibold text-slate-700 mb-2">Client</label>
                                <div className="relative">
                                    <select
                                        {...clientRegister}
                                        onChange={(e) => {
                                            if (e.target.value === 'new_client') {
                                                setShowAddClient(true);
                                            } else {
                                                onClientChange(e);
                                            }
                                        }}
                                        className="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all appearance-none text-slate-700 font-medium"
                                        required
                                    >
                                        <option value="">Choisir un client...</option>
                                        <option value="new_client" className="text-blue-600 font-bold bg-blue-50">+ Nouveau Client</option>
                                        <optgroup label="Clients existants">
                                            {clients.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                                        </optgroup>
                                    </select>
                                    <div className="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-500">
                                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                            </div>

                            {/* Total Control */}
                            <div className="md:col-span-2 border-t border-gray-100 pt-6 mt-2">
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
                                            Modifier le montant total manuellement
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

                        {/* Services List */}
                        <div className="space-y-6">
                            <div className="flex items-center justify-between px-2">
                                <h3 className="text-lg font-bold text-slate-700">Services & Prestations</h3>
                                <span className="text-xs font-medium text-slate-400 uppercase tracking-widest">{fields.length} SERVICE(S)</span>
                            </div>

                            {fields.map((field, index) => {
                                const serviceType = watch(`services.${index}.service_id`);

                                return (
                                    <div key={field.id} className="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative transition-all hover:shadow-md group">
                                        <div className="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                            {fields.length > 1 && (
                                                <button type="button" onClick={() => remove(index)} className="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Retirer ce service">
                                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            )}
                                        </div>

                                        <div className="flex items-center gap-3 mb-6">
                                            <div className="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                                                {index + 1}
                                            </div>
                                            <h4 className="text-sm font-semibold text-slate-500 uppercase tracking-wider">Détails de la prestation</h4>
                                        </div>

                                        <div className="grid grid-cols-1 md:grid-cols-12 gap-6 mb-6">
                                            <div className="md:col-span-8">
                                                <label className="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Type de Service</label>
                                                <select
                                                    {...register(`services.${index}.service_id`)}
                                                    className="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-slate-700 font-medium"
                                                    required
                                                >
                                                    <option value="">Sélectionner...</option>
                                                    {servicesList.map(s => <option key={s.id} value={s.id}>{s.title}</option>)}
                                                </select>
                                            </div>
                                            <div className="md:col-span-4">
                                                <label className="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Prix Unitaire (MAD)</label>
                                                <div className="relative">
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        {...register(`services.${index}.unit_price`)}
                                                        className="w-full pl-4 pr-12 py-2.5 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none font-mono text-emerald-600 font-bold text-lg text-right"
                                                        placeholder="0.00"
                                                        required
                                                    />
                                                    <div className="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-emerald-600/50 font-bold text-xs">
                                                        MAD
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {/* Dynamic Fields */}
                                        <div className="bg-slate-50/50 p-5 rounded-xl border border-dashed border-slate-200 mb-6 space-y-4">
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                {(serviceType === '1') && (
                                                    <>
                                                        <div>
                                                            <input {...register(`services.${index}.from_location`)}
                                                                onChange={(e) => {
                                                                    register(`services.${index}.from_location`).onChange(e);
                                                                    handleSmartFill(index, 'from', e.target.value);
                                                                }}
                                                                placeholder="De (Ville départ)" className="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 focus:border-blue-500 outline-none" />
                                                        </div>
                                                        <div>
                                                            <input {...register(`services.${index}.to_location`)}
                                                                onChange={(e) => {
                                                                    register(`services.${index}.to_location`).onChange(e);
                                                                    handleSmartFill(index, 'to', e.target.value);
                                                                }}
                                                                placeholder="À (Ville arrivée)" className="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 focus:border-blue-500 outline-none" />
                                                        </div>
                                                    </>
                                                )}

                                                {(serviceType === '2' || serviceType === '3') && (
                                                    <div className="col-span-2">
                                                        <input {...register(`services.${index}.city`)}
                                                            onChange={(e) => {
                                                                register(`services.${index}.city`).onChange(e);
                                                                handleSmartFill(index, 'city', e.target.value);
                                                            }}
                                                            placeholder="Ville concernée" className="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 focus:border-blue-500 outline-none" />
                                                    </div>
                                                )}
                                            </div>

                                            {/* Custom Description */}
                                            <div>
                                                <label className="block text-[11px] font-bold text-blue-600 mb-2 uppercase tracking-wide flex items-center gap-2">
                                                    <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                    Description sur Facture
                                                </label>
                                                <textarea
                                                    {...register(`services.${index}.custom_desc`)}
                                                    rows="2"
                                                    className="w-full px-4 py-3 bg-blue-50/30 border border-blue-100 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-sm text-slate-700 shadow-sm transition-all"
                                                    placeholder="Description détaillée qui apparaîtra sur le PDF..."
                                                ></textarea>
                                            </div>
                                        </div>

                                        <div>
                                            <label className="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Date de la prestation</label>
                                            <input {...register(`services.${index}.service_date`)} type="date" className="w-full md:w-auto px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-600 outline-none focus:border-blue-500" />
                                        </div>
                                    </div>
                                )
                            })}
                        </div>

                        {/* Actions */}
                        <div className="flex flex-col sm:flex-row gap-4 pt-4 border-t border-gray-200">
                            <button
                                type="button"
                                onClick={() => append({ service_id: "", unit_price: "", custom_desc: "", service_date: new Date().toISOString().split('T')[0] })}
                                className="flex-1 py-4 border-2 border-dashed border-gray-300 rounded-xl text-gray-500 font-medium hover:border-blue-500 hover:text-blue-600 hover:bg-blue-50/50 transition-all flex justify-center items-center gap-2 group"
                            >
                                <div className="bg-gray-100 group-hover:bg-blue-100 rounded-full p-1 transition-colors">
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4"></path></svg>
                                </div>
                                Ajouter un autre Service
                            </button>
                            <button
                                type="submit"
                                className="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-4 rounded-xl shadow-xl shadow-blue-600/30 transition-all active:scale-[0.98] flex justify-center items-center gap-2"
                            >
                                <span className="text-lg">Sauvegarder les modifications</span>
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
