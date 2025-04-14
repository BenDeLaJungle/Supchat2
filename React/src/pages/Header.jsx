import React from 'react';
import SearchBar from './SearchBar';
import Logout from './logout';
import logo from '../assets/logo-supchat.png';
import '../styles/index.css'; // ou un CSS dédié

export default function Header() {
  return (
    <div className="header-home">
      {/* Logo à gauche */}
      <div className="header-logo">
        <img src={logo} alt="Logo Supchat" className="logo" />
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
