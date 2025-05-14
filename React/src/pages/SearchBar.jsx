import React, { useState, useEffect, useRef } from "react";
import { useNavigate } from "react-router-dom";
import { apiFetch } from "../services/api";
import '../styles/index.css';

const SearchBar = ({ onSearchChange }) => {
  const [searchTerm, setSearchTerm] = useState("");
  const [results, setResults] = useState([]);
  const [showDropdown, setShowDropdown] = useState(false);
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(false);
  const searchRef = useRef(null);
  const navigate = useNavigate();
  const debounceTimeout = useRef(null);

  
  const handleSearch = (term) => {
    if (debounceTimeout.current) {
      clearTimeout(debounceTimeout.current);
    }
    debounceTimeout.current = setTimeout(() => {
      if (term.trim() !== "") {
        setLoading(true);
        apiFetch(`api/users/search?query=${term}`)
          .then((data) => {
            setResults(data);
            setShowDropdown(true);
            setLoading(false);
            setError(null);
          })
          .catch((err) => {
            console.error("Erreur lors de la récupération de l'utilisateur", err);
            setError("Impossible de récupérer les utilisateurs.");
            setResults([]);
            setLoading(false);
          });
      } else {
        setResults([]);
        setShowDropdown(false);
      }
    }, 300); 
  };

  // Mise à jour à chaque changement de texte
  useEffect(() => {
    handleSearch(searchTerm);
  }, [searchTerm]);

  // Gestion du clic à l'extérieur du dropdown
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (searchRef.current && !searchRef.current.contains(event.target)) {
        setShowDropdown(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  // Sélection d'un utilisateur
  const handleSelectUser = (user) => {
    setShowDropdown(false);
    navigate(`/user`, { state: { user } });
  };

  return (
    <div className="search-bar-wrapper" ref={searchRef}>
      <input
        type="text"
        className="search-input"
        placeholder=" Rechercher un utilisateur..."
        value={searchTerm}
        onChange={(e) => setSearchTerm(e.target.value)}
        onFocus={() => setShowDropdown(results.length > 0)}
      />

      {loading && <div className="loader">Chargement...</div>}

      {error && <p className="text-red-500">{error}</p>}

      {showDropdown && (
        <div className="search-dropdown">
          {results.length > 0 ? (
            results.map((user) => (
              <div
                key={user.id}
                className="search-dropdown-item"
                onClick={() => handleSelectUser(user)}
              >
                {user.username} {user.firstName} {user.lastName}
              </div>
            ))
          ) : (
            !loading && <div className="no-results">Aucun utilisateur trouvé.</div>
          )}
        </div>
      )}
    </div>
  );
};

export default SearchBar;
