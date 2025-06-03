import React, { useState, useEffect, useRef } from "react";
import { useNavigate } from "react-router-dom";
import { apiFetch } from "../../services/api";

const SearchBar = () => {
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
      const trimmed = term.trim();
      if (trimmed !== "") {
        setLoading(true);
        apiFetch(`users/search?query=${encodeURIComponent(trimmed)}`)
          .then((data) => {
            setResults(data);
            setShowDropdown(data.length > 0);
            setLoading(false);
            setError(null);
          })
          .catch((err) => {
            console.error("Erreur lors de la récupération des utilisateurs", err);
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

  useEffect(() => {
    handleSearch(searchTerm);
  }, [searchTerm]);

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

  const handleSelectUser = (user) => {
    setShowDropdown(false);
    navigate(`/users/${encodeURIComponent(user.userName)}`);
  };

  return (
    <div className="search-bar-wrapper" ref={searchRef}>
      <input
        type="text"
        className="search-input"
        placeholder="Rechercher un utilisateur…"
        value={searchTerm}
        onChange={(e) => setSearchTerm(e.target.value)}
        onFocus={() => {
          if (results.length > 0) {
            setShowDropdown(true);
          }
        }}
      />

      {loading && <div className="loader">Chargement…</div>}
      {error && <p className="search-error">{error}</p>}

      {showDropdown && (
        <div className="search-dropdown">
          {results.length > 0 ? (
            results.map((user) => (
              <div
                key={user.id}
                className="search-dropdown-item"
                onClick={() => handleSelectUser(user)}
              >
                {user.userName}
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
