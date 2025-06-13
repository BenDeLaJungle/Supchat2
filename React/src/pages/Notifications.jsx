import React, { useEffect, useState } from 'react';
import { apiFetch } from '../services/api';
import AdminHeader from '../components/ui/Adminheader';


export default function Notifications() {
  const [notifications, setNotifications] = useState([]);
  const [error, setError] = useState(null);

  const fetchNotifications = async () => {
    try {
      const data = await apiFetch('notifications/unread');
      setNotifications(data);
    } catch (err) {
      console.error("Erreur lors du chargement des notifications :", err.message);
      setError("Impossible de charger les notifications.");
    }
  };

  useEffect(() => {
    fetchNotifications();
  }, []);

  return (
    <>
      <AdminHeader />
      <div className="notifications-container">
        <h2 className="notifications-title">Notifications non lues</h2>

        {error && <div className="message-error">{error}</div>}

        {notifications.length === 0 ? (
          <p>Aucune notification pour le moment.</p>
        ) : (
          <ul className="notifications-list">
            {notifications.map((notif) => (
              <li key={notif.id} className="notification-item unread">
                <p>{notif.message.length > 100 ? notif.message.substring(0, 100) + '...' : notif.message}</p>
                <p>Status : {notif.read ? "Lu" : "Non lu"}</p>
              </li>
            ))}
          </ul>
        )}
      </div>
    </>
  );
}
