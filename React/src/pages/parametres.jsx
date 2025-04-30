import React, { useState, useEffect } from 'react';
import '../styles/parametres.css';
import { useAuth } from '../context/AuthContext';
import { apiFetch } from '../services/api';
import AdminHeader from './Adminheader';

export default function Parametres() {
  const { user, setUser } = useAuth();
  const [username, setUsername] = useState(user?.username || '');
  const [email, setEmail] = useState(user?.email || '');
  const [status, setStatus] = useState(user?.status || '');
  const [theme, setTheme] = useState(user?.theme ? 'dark' : 'light');  

  const [successUpdateInfos, setSuccessUpdateInfos] = useState('');
  const [errorUpdateInfos, setErrorUpdateInfos] = useState('');
  const [successUpdateStatut, setSuccessUpdateStatut] = useState('');
  const [errorUpdateStatut, setErrorUpdateStatut] = useState('');
  const [errorExport, setErrorExport] = useState('');
  
  useEffect(() => {
    document.body.className = `theme-${theme}`;
  }, [theme]);


  const handleUpdate = async (e) => {
    e.preventDefault();
    setSuccessUpdateInfos('');
    setErrorUpdateInfos('');

    try {
      await apiFetch('api/user', {
        method: 'PUT',
        body: JSON.stringify({ userName: username, emailAddress: email }),
      });

      setUser({ ...user, username, email });
      setSuccessUpdateInfos('Informations mises à jour !');
    } catch (err) {
      console.error(err);
      setErrorUpdateInfos('Erreur lors de la mise à jour.');
    }
  };

  const handleStatusUpdate = async (e) => {
    e.preventDefault();
    setSuccessUpdateStatut('');
    setErrorUpdateStatut('');

    try {
      await apiFetch('api/user', {
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

  const handleExportData = async () => {
    try {
      const data = await apiFetch('api/user');
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

  const toggleTheme = async () => {
    const newTheme = theme === 'light' ? 'dark' : 'light';
    setTheme(newTheme);
    document.body.className = `theme-${newTheme}`; 
  
    try {
      await apiFetch('api/user', {
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
              <form onSubmit={handleStatusUpdate}>
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

          {/* Bloc droite */}
          <div className="bloc-droite">

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
