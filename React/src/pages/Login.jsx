import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import { apiFetch } from "../services/api";
import { login } from "../services/auth";
import { useAuth } from "../context/AuthContext";
import "../styles/Login.css";
import "../styles/color.css";

const Login = () => {
  const [email, setEmail] = useState("");
  const [mdp, setMdp] = useState("");
  const [error, setError] = useState("");
  const navigate = useNavigate();
  const { setUser } = useAuth();

  const fetchAndSetUser = async (token) => {
    try {
      const res = await apiFetch("/api/auth/me", {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });
      const user = await res.json();
      setUser(user);
      navigate("/home");
    } catch (err) {
      console.error("Erreur lors de la récupération de l'utilisateur :", err);
      setError("Impossible de récupérer les informations utilisateur.");
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");

    try {
      const data = await apiFetch("/auth/login", {
        method: "POST",
        body: JSON.stringify({
          emailAddress: email,
          password: mdp,
        }),
      });

      const { token } = data;

      login(token); // stocker le token
      await fetchAndSetUser(token); // récupérer l'utilisateur
    } catch (err) {
      console.error("❌ Erreur lors de la connexion :", err);
      setError(err.message || "Une erreur est survenue...");
    }
  };

  const openOAuthWindow = (provider) => {
    const width = 500;
    const height = 600;
    const left = window.innerWidth / 2 - width / 2;
    const top = window.innerHeight / 2 - height / 2;

    const authWindow = window.open(
      `http://localhost:8000/api/auth/${provider}`, // ⚠️ à adapter selon ton backend
      `${provider} Login`,
      `width=${width},height=${height},top=${top},left=${left}`
    );

    const handleMessage = async (event) => {
      if (event.origin !== "http://localhost:8000") return; // sécuriser selon ton domaine

      const { token } = event.data;
      if (token) {
        login(token);
        await fetchAndSetUser(token);
        window.removeEventListener("message", handleMessage);
        authWindow.close();
      }
    };

    window.addEventListener("message", handleMessage);
  };

  return (
    <div className="login-container">
      <h1 className="title">Connexion</h1>
      <form className="login-form" onSubmit={handleSubmit}>
        <input
          type="email"
          placeholder="Email"
          className="input"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
        />
        <input
          type="password"
          placeholder="MDP"
          className="input"
          value={mdp}
          onChange={(e) => setMdp(e.target.value)}
        />
        <button type="submit" className="btn">
          Se connecter
        </button>
        {error && <p className="error">{error}</p>}
      </form>

      <div className="social-login">
        <img
          src="https://cdn-icons-png.flaticon.com/512/2991/2991148.png"
          alt="Google"
          className="icon"
          onClick={() => openOAuthWindow("google")}
        />
        <img
          src="https://cdn-icons-png.flaticon.com/512/145/145802.png"
          alt="Facebook"
          className="icon"
          onClick={() => openOAuthWindow("facebook")}
        />
      </div>
    </div>
  );
};

export default Login;
