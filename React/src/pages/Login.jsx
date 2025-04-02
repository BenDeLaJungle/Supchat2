import React, { useState } from "react";
import api from "../services/axios";
import "../styles/Login.css";
import "../styles/color.css";
import { useNavigate } from "react-router-dom";



const Login = () => {

  //API
  const [email, setEmail] = useState("");
  const [mdp, setMdp] = useState("");
  const [error, setError] = useState("");
  const navigate = useNavigate();


  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
  
    try {
      const response = await api.post("/auth/login", {
        emailAddress: email,
        password: mdp,
      });
  
      const { token, user } = response.data;
  
      // 🪄 Stocker le token et le user
      localStorage.setItem("token", token);
      localStorage.setItem("user", JSON.stringify(user));
  
      // ✅ Log succès
      console.log("%c✅ Connexion réussie !", "color: green; font-weight: bold;");
      console.log("🎉 Utilisateur connecté :", user);
  
      alert("Connexion réussie ! Bienvenue " + user.userName + " 💕");
      navigate("/home")
    } catch (err) {
      console.error("❌ Erreur lors de la connexion :", err);
  
      if (err.response && err.response.data?.error) {
        setError(err.response.data.error);
        console.warn("⚠️ Détail :", err.response.data.error);
      } else {
        setError("Une erreur est survenue...");
        console.warn("⚠️ Erreur inconnue");
      }
    }
  };

  //HTML
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
        <button type="submit" className="btn">Se connecter</button>
      </form>
      <div className="social-login">
        <img src="https://cdn-icons-png.flaticon.com/512/2991/2991148.png" alt="Google" className="icon" />
        <img src="https://cdn-icons-png.flaticon.com/512/145/145802.png" alt="Facebook" className="icon" />
      </div>
    </div>
  );

};

export default Login;