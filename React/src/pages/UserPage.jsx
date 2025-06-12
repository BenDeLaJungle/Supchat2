import React, { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { apiFetch } from "../services/api";
import AdminHeader from "../components/ui/Adminheader";
import { useAuth } from "../context/AuthContext";
import "../styles/User.css";

const workspaceId = 1;

export default function UserPage() {
  const { username } = useParams();
  const navigate = useNavigate();
  const { user: currentUser } = useAuth();

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

  const handleSendMessage = async () => {
    if (!userInfo || !currentUser) return;

    try {
      const channel = await apiFetch("channels/simple", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          workspace_id: workspaceId,
          status: false,
          participants: [currentUser.id, userInfo.id]
        })
      });
      navigate(`/messaging?channel=${channel.id}`);
    } catch (err) {
      console.error("Impossible de démarrer la conversation :", err);
      alert("Une erreur est survenue. Réessayez.");
    }
  };

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

            {/* Bouton “Envoyer un message” */}
            <button
              className="user-card-button"
              onClick={handleSendMessage}
            >
              ✉️ Envoyer un message
            </button>
          </div>
        )}
      </div>
    </>
  );
}
