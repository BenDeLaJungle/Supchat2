import React, { useState, useEffect, useRef } from "react";
import { useNavigate } from "react-router-dom";
import { apiFetch } from "../../services/api";
import '../../styles/AdminBar.css';


const SearchBar = () => {
  const [searchTerm, setSearchTerm] = useState("");
  const [results, setResults] = useState({
    users: [],
    channels: [],
    files: [],
    messages: [],
  });
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
              channels: data.channels || [],
              files: data.files || [],
              messages: data.messages || [],
            });
            setShowDropdown(true);
            setLoading(false);
            setError(null);
          })
          .catch((err) => {
            console.error("Erreur lors de la recherche :", err);
            setError("Erreur lors de la recherche.");
            setResults({ users: [], channels: [], files: [], messages: [] });
            setLoading(false);
          });
      } else {
        setResults({ users: [], channels: [], files: [], messages: [] });
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
      case "channel":
        navigate(`/channels/${item.id}`);
        break;
      case "file":
        navigate(`/files/${item.id}`);
        break;
      case "message":
        navigate(`/channels/${item.channelId}#message-${item.id}`);
        break;
      default:
        break;
    }
  };

  return (
    <div className="searchbar-admin" ref={searchRef}>
      <input
        type="text"
        className="search-input"
        placeholder="Rechercher un utilisateur, un canal, un fichier ou un message…"
        value={searchTerm}
        onChange={(e) => setSearchTerm(e.target.value)}
        onFocus={() => {
          const hasResults =
            results.users.length ||
            results.channels.length ||
            results.files.length ||
            results.messages.length;
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
                <div
                  key={`user-${user.id}`}
                  className="search-dropdown-item"
                  onClick={() => handleSelect(user, "user")}
                >
                  {user.userName}
                </div>
              ))}
            </>
          )}

          {results.channels.length > 0 && (
            <>
              <div className="search-category">Canaux</div>
              {results.channels.map((ch) => (
                <div
                  key={`ch-${ch.id}`}
                  className="search-dropdown-item"
                  onClick={() => handleSelect(ch, "channel")}
                >
                  {ch.name}
                </div>
              ))}
            </>
          )}

          {results.files.length > 0 && (
            <>
              <div className="search-category">Fichiers partagés</div>
              {results.files.map((file) => (
                <div
                  key={`file-${file.id}`}
                  className="search-dropdown-item"
                  onClick={() => handleSelect(file, "file")}
                >
                  {file.name}
                </div>
              ))}
            </>
          )}

          {results.messages.length > 0 && (
            <>
              <div className="search-category">Messages</div>
              {results.messages.map((msg) => (
                <div
                  key={`msg-${msg.id}`}
                  className="search-dropdown-item"
                  onClick={() => handleSelect(msg, "message")}
                >
                  {msg.preview?.trim() || msg.content?.substring(0, 50) + "…"}
                </div>
              ))}
            </>
          )}

          {Object.values(results).every((arr) => arr.length === 0) && !loading && (
            <div className="no-results">Aucun résultat trouvé.</div>
          )}
        </div>
      )}
    </div>
  );
};

export default SearchBar;
