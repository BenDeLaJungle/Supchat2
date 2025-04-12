import React from "react";
import '../styles/index.css';


const SearchBar = () => {
  return (
    <div className="search-bar-wrapper">
      <input
        type="text"
        className="search-input"
        placeholder="🔍 Rechercher un message, un fichier, un utilisateur..."
      />
    </div>
  );
};

export default SearchBar;
