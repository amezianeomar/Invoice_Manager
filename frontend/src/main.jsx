import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import axios from 'axios'
import './index.css'
import App from './App.jsx'

// Set base URL based on environment
const isProduction = import.meta.env.PROD;
// REPLACE WITH YOUR ALWAYS DATA URL AFTER DEPLOYMENT
const productionUrl = 'https://YOUR_ALWAYSDATA_ACCOUNT.alwaysdata.net/api';

axios.defaults.baseURL = isProduction ? productionUrl : '/api';

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <App />
  </StrictMode>,
)
