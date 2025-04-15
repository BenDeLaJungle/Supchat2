import React from 'react';
import { Link } from 'react-router-dom';
import SearchBar from './SearchBar';
import Logout from './logout';
import logo from '../assets/logo-supchat.png';
import '../styles/index.css';

export default function Header() {
  return (
    <div className="header-home">
      {/* Logo cliquable à gauche */}
      <div className="header-logo">
        <Link to="/home">
          <img src={logo} alt="Logo Supchat" className="logo" />
        </Link>
      </div>

      {/* Barre de recherche au centre */}
      <div className="header-barre">
        <SearchBar />
      </div>

      {/* Bouton logout à droite */}
      <div className="header-logout">
        <Logout />
      </div>
    </div>
  );
}
