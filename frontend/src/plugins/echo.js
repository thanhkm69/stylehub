import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import axios from 'axios'

const reverbEnabled = import.meta.env.VITE_REVERB_ENABLED === 'true'

window.Pusher = Pusher

const echo = reverbEnabled ? new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authorizer: (channel, options) => {
        return {
            authorize: (socketId, callback) => {
                axios.post(import.meta.env.VITE_API_URL + '/broadcasting/auth', {
                    socket_id: socketId,
                    channel_name: channel.name
                }, {
                    headers: {
                        Authorization: `Bearer ${(localStorage.getItem('token') || '').replace(/"/g, '')}`
                    }
                })
                .then(response => {
                    callback(false, response.data);
                })
                .catch(error => {
                    callback(true, error);
                });
            }
        };
    },
}) : null

export default echo
