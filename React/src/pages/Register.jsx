import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import { apiFetch } from "../services/api";
import "../styles/Login.css";

const Register = () => {
  const [firstName, setFirstName] = useState("");
  const [lastName, setLastName] = useState("");
  const [userName, setUserName] = useState("");
  const [email, setEmail] = useState("");
  const [mdp, setMdp] = useState("");
  const [confirmMdp, setConfirmMdp] = useState("");
  const [error, setError] = useState("");
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");

    if (mdp !== confirmMdp) {
      setError("Les mots de passe ne correspondent pas.");
      return;
    }

    try {
      await apiFetch("api/auth/register", {
        method: "POST",
        body: JSON.stringify({
          firstName,
          lastName,
          userName,
          emailAddress: email,
          password: mdp,
        }),
      });
      navigate("/login");
    } catch (err) {
      setError(err.message || "Une erreur est survenue...");
    }
  };

  return (
    <div className="login-container">
      <h1 className="title">Inscription</h1>
      <form className="login-form" onSubmit={handleSubmit}>
        <input
          type="text"
          placeholder="Prénom"
          className="input"
          value={firstName}
          onChange={(e) => setFirstName(e.target.value)}
        />
        <input
          type="text"
          placeholder="Nom"
          className="input"
          value={lastName}
          onChange={(e) => setLastName(e.target.value)}
        />
        <input
          type="text"
          placeholder="Nom d'utilisateur"
          className="input"
          value={userName}
          onChange={(e) => setUserName(e.target.value)}
        />
        <input
          type="email"
          placeholder="Email"
          className="input"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
        />
        <input
          type="password"
          placeholder="Mot de passe"
          className="input"
          value={mdp}
          onChange={(e) => setMdp(e.target.value)}
        />
        <input
          type="password"
          placeholder="Confirmer MDP"
          className="input"
          value={confirmMdp}
          onChange={(e) => setConfirmMdp(e.target.value)}
        />
        <button type="submit" className="btn">S'inscrire</button>
        {error && <p className="error">{error}</p>}
      </form>
    </div>
  );
};

export default Register;
