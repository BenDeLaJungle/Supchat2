import React, { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { apiFetch } from "../services/api";
import AdminHeader from "../components/ui/Adminheader";
import "../styles/User.css";

export default function UserPage() {
  const { username } = useParams();

  const [userInfo, setUserInfo] = useState(null);
  const [error, setError] = useState(null);

  useEffect(() => {
    async function fetchUser() {
      try {
        const data = await apiFetch(
          `users/by-username/${encodeURIComponent(username)}`
        );
        setUserInfo(data);
      } catch (err) {
        console.error(
          "Erreur lors de la récupération des infos de l’utilisateur : ",
          err
        );
        setError(err.message || "Impossible de charger l’utilisateur");
      }
    }

    fetchUser();
  }, [username]);

  return (
    <>
      <AdminHeader />

      <div className="user-page-container">
        {error && (
          <div className="user-error">
            <p>⚠️ {error}</p>
          </div>
        )}

        {!userInfo && !error && (
          <div className="user-page-loading">Chargement…</div>
        )}

        {userInfo && (
          <div className="user-card">
            <h2 className="user-card-title">
              Informations de : {userInfo.username}
            </h2>
            <div className="user-card-content">
              <p>
                <strong>Nom d'utilisateur :</strong> {userInfo.username}
              </p>
              <p>
                <strong>Email :</strong> {userInfo.email}
              </p>
              <p>
                <strong>Rôle :</strong> {userInfo.role}
              </p>
              <p>
                <strong>Thème :</strong>{" "}
                {userInfo.theme === "dark" ? "Sombre" : "Clair"}
              </p>
              <p>
                <strong>Statut :</strong> {userInfo.status ? "Actif" : "Inactif"}
              </p>
              <p>
                <strong>Prénom :</strong> {userInfo.firstName}
              </p>
              <p>
                <strong>Nom :</strong> {userInfo.lastName}
              </p>
            </div>
            <button className="user-card-button">✉️ Envoyer un message</button>
          </div>
        )}
      </div>
    </>
  );
}
