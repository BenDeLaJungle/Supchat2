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
  const [success, setSuccess] = useState("");
  const navigate = useNavigate();

  const isPasswordStrong = (pwd) => {
    return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,}$/.test(pwd);
  };

  const getPasswordStrength = (pwd) => {
    let strength = 0;
    if (pwd.length >= 8) strength++;
    if (/[A-Z]/.test(pwd)) strength++;
    if (/[0-9]/.test(pwd)) strength++;
    if (/[^\w\s]/.test(pwd)) strength++;
    return strength;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
    setSuccess("");

    if (mdp !== confirmMdp) {
      setError("Les mots de passe ne correspondent pas.");
      return;
    }

    if (!isPasswordStrong(mdp)) {
      setError("Le mot de passe doit contenir au moins 8 caractères, une majuscule, un chiffre et un caractère spécial.");
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
      setSuccess("Compte créé avec succès ! Redirection en cours...");
      setTimeout(() => navigate("/login"), 2000);
    } catch (err) {
      setError(err.message || "Une erreur est survenue...");
    }
  };

  const strength = getPasswordStrength(mdp);
  const strengthLabel = ["Très faible", "Faible", "Moyen", "Bon", "Très bon"];
  const strengthColor = ["#e74c3c", "#e67e22", "#f1c40f", "#2ecc71", "#27ae60"];

  return (
    <div className="login-container">
      <h1 className="title">Inscription</h1>
      <form className="login-form" onSubmit={handleSubmit}>
        <input type="text" placeholder="Prénom" className="input" value={firstName} onChange={(e) => setFirstName(e.target.value)} />
        <input type="text" placeholder="Nom" className="input" value={lastName} onChange={(e) => setLastName(e.target.value)} />
        <input type="text" placeholder="Nom d'utilisateur" className="input" value={userName} onChange={(e) => setUserName(e.target.value)} />
        <input type="email" placeholder="Email" className="input" value={email} onChange={(e) => setEmail(e.target.value)} />
        <input type="password" placeholder="Mot de passe" className="input" value={mdp} onChange={(e) => setMdp(e.target.value)} />

        <div className="password-strength">
          <div
            className="strength-bar"
            style={{
              width: `${(strength / 4) * 100}%`,
              backgroundColor: strengthColor[strength],
              height: "8px",
              borderRadius: "4px",
              marginBottom: "5px",
            }}
          ></div>
          <small style={{ color: strengthColor[strength] }}>{strengthLabel[strength]}</small>
        </div>

        <input type="password" placeholder="Confirmer MDP" className="input" value={confirmMdp} onChange={(e) => setConfirmMdp(e.target.value)} />
        <button type="submit" className="btn">S'inscrire</button>
        {error && <p className="error">{error}</p>}
        {success && <p className="success">{success}</p>}
      </form>
    </div>
  );
};

export default Register;
