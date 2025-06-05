import React, { useState, useEffect, useRef } from "react";
import { useNavigate } from "react-router-dom";
import { apiFetch } from "../../services/api";

const SearchBar = () => {
  const [searchTerm, setSearchTerm] = useState("");
  const [results, setResults] = useState({ users: [], workspaces: [], channels: [] });
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
        apiFetch(`search?query=${encodeURIComponent(trimmed)}`)
          .then((data) => {
            setResults({
              users: data.users || [],
              workspaces: data.workspaces || [],
              channels: data.channels || [],
            });
            setShowDropdown(true);
            setLoading(false);
            setError(null);
          })
          .catch((err) => {
            console.error("Erreur lors de la recherche :", err);
            setError("Erreur lors de la recherche.");
            setResults({ users: [], workspaces: [], channels: [] });
            setLoading(false);
          });
      } else {
        setResults({ users: [], workspaces: [], channels: [] });
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

  const handleSelect = (item, type) => {
    setShowDropdown(false);
    switch (type) {
      case "user":
        navigate(`/users/${encodeURIComponent(item.userName)}`);
        break;
      case "workspace":
        navigate(`/workspaces/${item.id}`);
        break;
      case "channel":
        navigate(`/channels/${item.id}`);
        break;
      default:
        break;
    }
  };

  return (
    <div className="search-bar-wrapper" ref={searchRef}>
      <input
        type="text"
        className="search-input"
        placeholder="Rechercher un utilisateur, un workspace ou un canal…"
        value={searchTerm}
        onChange={(e) => setSearchTerm(e.target.value)}
        onFocus={() => {
          const hasResults = results.users.length || results.workspaces.length || results.channels.length;
          if (hasResults) setShowDropdown(true);
        }}
      />

      {loading && <div className="loader">Chargement…</div>}
      {error && <p className="search-error">{error}</p>}

      {showDropdown && (
        <div className="search-dropdown">
          {results.users.length > 0 && (
            <>
              <div className="search-category">Utilisateurs</div>
              {results.users.map((user) => (
                <div key={`user-${user.id}`} className="search-dropdown-item" onClick={() => handleSelect(user, "user")}>
                   {user.userName}
                </div>
              ))}
            </>
          )}

          {results.workspaces.length > 0 && (
            <>
              <div className="search-category">Workspaces</div>
              {results.workspaces.map((ws) => (
                <div key={`ws-${ws.id}`} className="search-dropdown-item" onClick={() => handleSelect(ws, "workspace")}>
                   {ws.name}
                </div>
              ))}
            </>
          )}

          {results.channels.length > 0 && (
            <>
              <div className="search-category">Canaux</div>
              {results.channels.map((ch) => (
                <div key={`ch-${ch.id}`} className="search-dropdown-item" onClick={() => handleSelect(ch, "channel")}>
                   {ch.name}
                </div>
              ))}
            </>
          )}

          {results.users.length === 0 && results.workspaces.length === 0 && results.channels.length === 0 && !loading && (
            <div className="no-results">Aucun résultat trouvé.</div>
          )}
        </div>
      )}
    </div>
  );
};

export default SearchBar;
