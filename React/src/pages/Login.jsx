import React, { useState, useEffect } from "react";
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
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();
  const { user, setUser } = useAuth();

  const fetchAndSetUser = async (token) => {
    try {
      const user = await apiFetch("user", {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });
      setUser(user);
      navigate("/home");
    } catch (err) {
      console.error("Erreur lors de la récupération de l'utilisateur :", err);
      setError("Impossible de récupérer les informations utilisateur.");
    } finally {
      setLoading(false);
    }
  };

  // Rediriger si déjà connecté
  useEffect(() => {
    if (user) {
      navigate("/home");
    } else {
      setLoading(false);
    }
  }, [user, navigate]);

  // Gérer le token passé en URL (OAuth fallback)
  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    const token = params.get("token");

    if (token) {
      login(token);
      fetchAndSetUser(token);

      const cleanUrl = window.location.origin + window.location.pathname;
      window.history.replaceState(null, "", cleanUrl);
    } else {
      setLoading(false);
    }
  }, []);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");

    try {
      const data = await apiFetch("auth/login", {
        method: "POST",
        body: JSON.stringify({
          emailAddress: email,
          password: mdp,
        }),
      });

      const { token } = data;
      if (!token) throw new Error("Token manquant dans la réponse");

      login(token);
      await fetchAndSetUser(token);
    } catch (err) {
      console.error("Erreur lors de la connexion :", err);
      setError(err.message || "Une erreur est survenue...");
    }
  };

  const openOAuthWindow = (provider) => {
    const width = 500;
    const height = 600;
    const left = window.innerWidth / 2 - width / 2;
    const top = window.innerHeight / 2 - height / 2;
	const addr = 'https://127.0.0.1:8000';

    const authWindow = window.open(
      `${addr}/api/auth/${provider}`,
      `${provider} Login`,
      `width=${width},height=${height},top=${top},left=${left}`
    );

    const handleMessage = async (event) => {
      if (event.origin !== "https://127.0.0.1:8000") return;


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

  if (loading) return <p>Chargement...</p>;

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
		<p className="signup-link">
			Pas encore inscrit ?{" "}
			<span onClick={() => navigate("/register")} style={{ color: "#007bff", cursor: "pointer" }}>
				Créez un compte
			</span>
		</p>
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
