import React, { useEffect, useState } from 'react';

function Notifications() {
    const [notifications, setNotifications] = useState([]);
    const [loading, setLoading] = useState(true);

    const fetchNotifications = async () => {
        try {
            const response = await fetch('notifications/unread', {
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'Accept': 'application/json',
                }
            });

            if (!response.ok) {
                throw new Error('Erreur lors du chargement');
            }

            const data = await response.json();
            setNotifications(data);
        } catch (error) {
            console.error('Erreur de chargement des notifications :', error);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchNotifications();
        const interval = setInterval(fetchNotifications, 10000); // refresh toutes les 10s
        return () => clearInterval(interval);
    }, []);

    if (loading) return (
        <div style={{
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center',
            minHeight: '50vh',
            textAlign: 'center'
        }}>
            <p>Chargement...</p>
        </div>
    );

    return (
        <div style={{
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            justifyContent: 'center',
            minHeight: '50vh',
            textAlign: 'center'
        }}>
            <h4>Notifications</h4>
            {notifications.length === 0 ? (
                <p>Aucune notification non lue.</p>
            ) : (
                <ul>
                    {notifications.map((notif) => (
                        <li key={notif.id}>
                            📩 {notif.message}
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}

export default Notifications;
