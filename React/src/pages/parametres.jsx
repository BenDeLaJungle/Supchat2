import React, { useState } from 'react';
import Header from './Header';
import '../styles/parametres.css';
import { useAuth } from '../context/AuthContext';
import { apiFetch } from '../services/api';

export default function Parametres() {
  const { user, setUser } = useAuth();
  const [username, setUsername] = useState(user?.username || '');
  const [email, setEmail] = useState(user?.email || '');
  const [status, setStatus] = useState(user?.status || '');
  const [success, setSuccess] = useState('');
  const [error, setError] = useState('');

  const handleUpdate = async (e) => {
    e.preventDefault();
    setSuccess('');
    setError('');

    try {
      await apiFetch('api/user/update', {
        method: 'PUT',
        body: JSON.stringify({ username, email }),
      });

      setUser({ ...user, username, email });
      setSuccess("Informations mises à jour !");
    } catch (err) {
      console.error(err);
      setError("Erreur lors de la mise à jour.");
    }
  };

  const handleStatusUpdate = async (e) => {
    e.preventDefault();
    try {
      await apiFetch('api/user/update', {
        method: 'PUT',
        body: JSON.stringify({ status }),
      });
      setUser({ ...user, status });
      setSuccess("Statut mis à jour !");
    } catch (err) {
      console.error(err);
      setError("Erreur lors de la mise à jour du statut.");
    }
  };

  return (
    <>
      <Header />
      <h1 className="para-titre">Paramètres de votre compte</h1>
      <div className="parametres-wrapper">
      <div className="parametres-container">
        <div className="bloc-gauche">
          <div className="para compte">
            <h3>Gestion du compte</h3>
            <form onSubmit={handleUpdate} className="form-compte">
              <label>
                Nom d'utilisateur :
                <input
                  type="text"
                  value={username}
                  onChange={(e) => setUsername(e.target.value)}
                  required
                />
              </label>
              <label>
                Email :
                <input
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  required
                />
              </label>
              <button type="submit">Modifier</button>
              {success && <p className="success">{success}</p>}
              {error && <p className="error">{error}</p>}
            </form>
          </div>

          <div className="para rgpd">
            <h3>Exercice du droit RGPD</h3>
            <p>Vous pouvez exporter vos données personnelles.</p>
            <button>Exporter mes données</button>
            <details className="rgpd-infos">
              <summary>En savoir plus sur vos droits</summary>
              <p>
                Conformément au Règlement Général sur la Protection des Données (RGPD), vous pouvez demander l'accès à vos données personnelles, leur rectification ou leur suppression.
                <br /><br />
                Nous collectons uniquement les informations nécessaires au bon fonctionnement de la plateforme (nom, email, statut, préférences). Ces données ne sont partagées avec aucun tiers.
                <br /><br />
                Pour toute question ou demande, contactez-nous à l’adresse suivante :
                <a href="mailto:support@supchat.com"> support@supchat.com</a>.
              </p>
            </details>
          </div>
        </div>

        <div className="bloc-droite">
          <div className="para theme">
            <h3>Thème</h3>
            <p>Mode clair / sombre</p>
            <button>Changer le thème</button>
          </div>

          <div className="para statut">
            <h3>Gestion statut</h3>
            <form onSubmit={handleStatusUpdate}>
              <label htmlFor="status">Choisir un statut :</label>
              <select
                id="status"
                value={status}
                onChange={(e) => setStatus(e.target.value)}
              >
                <option value="Actif">Actif</option>
                <option value="En attente">En attente</option>
                <option value="Inactif">Inactif</option>
              </select>
              <button type="submit">Mettre à jour</button>
            </form>
          </div>
        </div>
      </div>
      </div>
    </>
  );
}
