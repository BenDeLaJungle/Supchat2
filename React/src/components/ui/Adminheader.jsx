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
import HeaderBar from "./HeaderBar.jsx"

import "../../styles/AdminBar.css";

const AdminHeader = () => {
  const navigate = useNavigate();
  const { user, setUser } = useAuth();
  const [lastTwoWorkspaces, setLastTwoWorkspaces] = useState([]);

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


  const shortcuts = [
    { title: "Messagerie",        image: messenger,     link: "/messaging"       },
    { title: "Fichiers",          image: files,         link: "/shared-files"    },
    { title: "Notifications",     image: notif,         link: "/notifications"   },
    { title: "Tous les workspaces", image: workspaceIcon, link: "/workspaces"     },
    { title: "Calendrier",         image: calendrier,    link: "/calendrier"      },
    ...(user?.role === "ROLE_ADMIN"
      ? [{ title: "Espace Admin", image: adminspace, link: "/AdminSpace" }]
      : [{ title: "Supchat",      image: logo,       link: "/home"         }]),
    { title: "Paramètres",        image: parametres,   link: "/parametres"     },
  ];

  return (
    <div className="header-admin">
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
            className="admin-shortcut-card"
            onClick={() => link && navigate(link)}
            title={title}
          >
            <img src={image} alt={title} />
          </div>
        ))}
        <div className="admin-search">
        <HeaderBar />
      </div>
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