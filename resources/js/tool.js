import axios from 'axios';
import Tool from './pages/Tool';

Nova.booting((app, store) => {
  Nova.inertia('NovaKanban', Tool)
})
app.axios = axios;
app.$http = axios;
window.axios = axios
