import React, { useState, useEffect, useRef } from "react";
import { useNavigate } from "react-router-dom";
import { apiFetch } from "../services/api";
import '../styles/index.css';

const SearchBar = ({ onSearchChange }) => {
  const [searchTerm, setSearchTerm] = useState("");
  const [results, setResults] = useState([]);
  const [showDropdown, setShowDropdown] = useState(false);
  const [error, setError] = useState(null);
  const navigate = useNavigate();
  const searchRef = useRef(null);

  useEffect(() => {
    if (searchTerm.trim() !== "") {
      apiFetch('api/admin/users')
        .then(data => {
          const filtered = data.filter(user =>
            user.username.toLowerCase().includes(searchTerm.toLowerCase()) ||
            user.email.toLowerCase().includes(searchTerm.toLowerCase())
          );
          setResults(filtered);
          setShowDropdown(true);
        })
        .catch(err => {
          console.error("Erreur lors de la récupération de l'utilisateur", err);
          setError("Impossible de récupérer les utilisateurs.");
          setResults([]);
        });
    } else {
      setResults([]);
      setShowDropdown(false);
    }
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

	  // On passe l'objet utilisateur directement dans le state de navigation
	  navigate(`/user`, { state: { user } });
	};

  return (
    <div className="search-bar-wrapper" ref={searchRef}>
      <input
        type="text"
        className="search-input"
        placeholder="🔍 Rechercher un utilisateur..."
        value={searchTerm}
        onChange={(e) => setSearchTerm(e.target.value)}
        onFocus={() => setShowDropdown(results.length > 0)}
      />

      {error && <p className='text-red-500'>{error}</p>}

      {showDropdown && results.length > 0 && (
        <div className="search-dropdown">
          {results.map((user) => (
            <div
              key={user.id}
              className="search-dropdown-item"
              onClick={() => handleSelectUser(user)}
            >
              {user.username} ({user.email})
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default SearchBar;
