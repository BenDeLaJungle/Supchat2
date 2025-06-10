// src/pages/Parametres.jsx

import React, { useState, useEffect } from 'react';
import '../styles/parametres.css';
import { useAuth } from '../context/AuthContext';
import { apiFetch } from '../services/api';
import AdminHeader from '../components/ui/Adminheader';

export default function Parametres() {
  const { user, setUser } = useAuth();

  // On détermine le thème initial à partir du localStorage ou de la data utilisateur (user.theme)
  const stored = localStorage.getItem('theme');
  const defaultFromApi = user?.theme ? 'dark' : 'light';
  const initialTheme =
    stored === 'dark' || stored === 'light'
      ? stored
      : defaultFromApi;

  const [theme, setTheme] = useState(initialTheme);
  const [username, setUsername] = useState(user?.username || '');
  const [email, setEmail] = useState(user?.email || '');
  const [status, setStatus] = useState(user?.status || '');
  const [successUpdateInfos, setSuccessUpdateInfos] = useState('');
  const [errorUpdateInfos, setErrorUpdateInfos] = useState('');
  const [successUpdateStatut, setSuccessUpdateStatut] = useState('');
  const [errorUpdateStatut, setErrorUpdateStatut] = useState('');
  const [errorExport, setErrorExport] = useState('');

  // À chaque changement de `theme`, on met à jour la classe du body ET localStorage
  useEffect(() => {
    document.body.className = `theme-${theme}`;
    localStorage.setItem('theme', theme);
  }, [theme]);

  // Met à jour le nom d'utilisateur et l'email
  const handleUpdate = async (e) => {
    e.preventDefault();
    setSuccessUpdateInfos('');
    setErrorUpdateInfos('');

    try {
      await apiFetch('user', {
        method: 'PUT',
        body: JSON.stringify({ userName: username, emailAddress: email }),
      });
      // On met à jour le contexte global user
      setUser({ ...user, username, email });
      setSuccessUpdateInfos('Informations mises à jour !');
    } catch (err) {
      console.error(err);
      setErrorUpdateInfos('Erreur lors de la mise à jour.');
    }
  };

  // Met à jour le statut
  const handleStatusUpdate = async (e) => {
    e.preventDefault();
    setSuccessUpdateStatut('');
    setErrorUpdateStatut('');

    try {
      await apiFetch('user', {
        method: 'PUT',
        body: JSON.stringify({ status }),
      });
      setUser({ ...user, status });
      setSuccessUpdateStatut('Statut mis à jour !');
    } catch (err) {
      console.error(err);
      setErrorUpdateStatut('Erreur lors de la mise à jour du statut.');
    }
  };

  // Exporte les données RGPD
  const handleExportData = async () => {
    try {
      const data = await apiFetch('user');
      const json = JSON.stringify(data, null, 2);
      const blob = new Blob([json], { type: 'application/json' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = 'mes_donnees_supchat.json';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
    } catch (err) {
      console.error('Erreur lors de l’exportation des données :', err);
      setErrorExport('Erreur lors de l’exportation des données.');
    }
  };

  // Bascule clair ↔ sombre
  const toggleTheme = async () => {
    const newTheme = theme === 'light' ? 'dark' : 'light';
    setTheme(newTheme);

    try {
      await apiFetch('user', {
        method: 'PUT',
        body: JSON.stringify({ theme: newTheme === 'dark' }),
      });
      setUser({ ...user, theme: newTheme === 'dark' });
    } catch (err) {
      console.error("Erreur lors du changement de thème :", err);
    }
  };

  return (
    <>
      <AdminHeader />

      <div className="parametres-wrapper">
        <div className="parametres-container">
          {/* Bloc gauche */}
          <div className="bloc-gauche">
            {/* Gestion statut */}
            <div className="para statut">
              <h3>Gestion statut</h3>
              <form onSubmit={handleStatusUpdate} className="form-statut">
                <select
                  id="status"
                  value={status}
                  onChange={(e) => setStatus(e.target.value)}
                >
                  <option value="Actif">En ligne</option>
                  <option value="En attente">Occupé</option>
                  <option value="Inactif">Hors ligne</option>
                </select>
                <button type="submit">Mettre à jour</button>
              </form>
              {successUpdateStatut && <p className="success">{successUpdateStatut}</p>}
              {errorUpdateStatut && <p className="error">{errorUpdateStatut}</p>}
            </div>

            {/* Exercice droit RGPD */}
            <div className="para rgpd">
              <h3>Exercice du droit RGPD</h3>
              <p>Vous pouvez exporter vos données personnelles.</p>
              <button onClick={handleExportData}>Exporter mes données</button>
              {errorExport && <p className="error">{errorExport}</p>}

              <details className="rgpd-infos">
                <summary>En savoir plus sur vos droits</summary>
                <p>
                  Conformément au Règlement Général sur la Protection des Données (RGPD),
                  vous pouvez demander l'accès à vos données personnelles, leur
                  rectification ou leur suppression.
                  <br /><br />
                  Nous collectons uniquement les informations nécessaires au bon
                  fonctionnement de la plateforme (nom, email, statut, préférences).
                  Ces données ne sont partagées avec aucun tiers.
                  <br /><br />
                  Pour toute question ou demande, contactez-nous à l’adresse suivante :
                  <a href="mailto:support@supchat.com"> support@supchat.com</a>.
                </p>
              </details>
            </div>
          </div>

          {/* Bloc droite */}
          <div className="bloc-droite">
            {/* Thème */}
            <div className="para theme">
              <h3>Thème</h3>
              <p>Mode {theme === 'dark' ? 'sombre' : 'clair'}</p>
              <button onClick={toggleTheme}>Changer le thème</button>
            </div>

            {/* Gestion du compte */}
            <div className="para compte">
              <h3>Gestion du compte</h3>
              <form onSubmit={handleUpdate} className="form-compte">
                <input
                  type="text"
                  value={username}
                  onChange={(e) => setUsername(e.target.value)}
                  placeholder="Nom d'utilisateur"
                  required
                />
                <input
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="Email"
                  required
                />
                <button type="submit">Modifier</button>
              </form>
              {successUpdateInfos && <p className="success">{successUpdateInfos}</p>}
              {errorUpdateInfos && <p className="error">{errorUpdateInfos}</p>}
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
