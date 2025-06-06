import React, { useEffect, useState } from "react";
import { useNavigate, Link } from "react-router-dom";
import { logout } from "../../services/auth";
import { useAuth } from "../../context/AuthContext";
import { apiFetch } from "../../services/api";

import messenger from "../../assets/messsage.png";
import files from "../../assets/share.png";
import notif from "../../assets/notif.png";
import workspaceIcon from "../../assets/workspace.png";
import calendrier from "../../assets/calendrier.png";
import adminspace from "../../assets/adminspace.png";
import parametres from "../../assets/settings.png";
import logo from "../../assets/logo-supchat.png";

import "../../styles/index.css";

const AdminHeader = () => {
  const navigate = useNavigate();
  const { user, setUser } = useAuth();
  const [lastTwoWorkspaces, setLastTwoWorkspaces] = useState([]);

  // Récupère tous les workspaces et ne garde que les 2 plus récents
  useEffect(() => {
    apiFetch("workspaces")
      .then((data) => {
        const sortedDesc = data.sort((a, b) => b.id - a.id);
        setLastTwoWorkspaces(sortedDesc.slice(0, 2));
      })
      .catch((err) => {
        console.error("Erreur fetch workspaces:", err);
      });
  }, []);

  const handleLogout = () => {
    logout();
    setUser(null);
    navigate("/login");
  };

  // On extrait workspace1 et workspace2 s'ils existent
  const ws1 = lastTwoWorkspaces[0];
  const ws2 = lastTwoWorkspaces[1];

  const shortcuts = [
    { title: "Messagerie", image: messenger, link: "/workspaces/1" },
    { title: "Fichiers ", image: files, link: "/shared-files" },
    { title: "Notifications", image: notif },
    ...(ws1
      ? [{ title: ws1.name, image: workspaceIcon, link: `/workspaces/${ws1.id}` }]
      : []),
    ...(ws2
      ? [{ title: ws2.name, image: workspaceIcon, link: `/workspaces/${ws2.id}` }]
      : []),
    { title: "Tous les workspaces", image: workspaceIcon, link: "/workspaces" },
    { title: "Calendrier", image: calendrier, link: "/calendrier" },
     ...(user?.role === 'ROLE_ADMIN'
          ? [{ title: "Espace Admin", image: adminspace, link: "/AdminSpace" }]
          : [{ title: "Supchat", image: logo }]
        ),
    { title: "Paramètres", image: parametres, link: "/parametres" },
  ];

  return (
    <div className="header-home">
      {/* Logo */}
      <div className="header-logo">
        <Link to="/home">
          <img src={logo} alt="Logo Supchat" className="logo" />
        </Link>
      </div>

      {/* Raccourcis */}
      <div className="header-barre">
        {shortcuts.map(({ title, image, link }) => (
          <div
            key={title}
            className="card"
            onClick={() => link && navigate(link)}
            title={title}
            style={{ width: "80px", height: "80px" }}
          >
            <img src={image} alt={title} style={{ width: 30, height: 30 }} />
            <h2 style={{ fontSize: "0.6rem", margin: 0 }}>{title}</h2>
          </div>
        ))}
      </div>

      {/* Déconnexion */}
      <div className="header-logout">
        <button className="logout-button" onClick={handleLogout}>
          Se déconnecter
        </button>
      </div>
    </div>
  );
};

export default AdminHeader;
