import React from "react";
import { useNavigate } from "react-router-dom";
import { logout } from "../services/auth";
import { useAuth } from "../context/AuthContext";
import messenger from "../assets/messsage.png";
import files from "../assets/share.png";
import notif from "../assets/notif.png";
import workspace from "../assets/workspace.png";
import calendrier from "../assets/calendrier.png";
import adminspace from "../assets/adminspace.png";
import parametres from "../assets/settings.png";
import logo from "../assets/logo-supchat.png";
import { Link } from "react-router-dom";

import "../styles/index.css";

const AdminHeader = () => {
  const navigate = useNavigate();
  const { setUser } = useAuth();

  const handleLogout = () => {
    logout();
    setUser(null);
    navigate("/login");
  };

  const shortcuts = [
    { title: "Messagerie", image: messenger, link: "/messaging" },
    { title: "Fichiers", image: files },
    { title: "Notifications", image: notif },
    { title: "Workspace 1", image: workspace },
    { title: "Workspace 2", image: workspace },
    { title: "Tous les workspaces", image: workspace },
    { title: "Calendrier", image: calendrier, link: "/calendrier" },
    { title: "Admin", image: adminspace, link: "/AdminSpace" },
    { title: "Paramètres", image: parametres },
  ];

  return (
    <div className="header-home">
      {/* Logo à gauche */}
      <div className="header-logo">
        <Link to="/home">
          <img src={logo} alt="Logo Supchat" className="logo" />
        </Link>
      </div>

      {/* Icônes au centre à la place de la barre de recherche */}
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

      {/* Déconnexion à droite */}
      <div className="header-logout">
        <button className="logout-button" onClick={handleLogout}>
          Se déconnecter
        </button>
      </div>
    </div>
  );
};

export default AdminHeader;
