import { BrowserRouter, Routes, Route } from "react-router-dom";
import Home from "../pages/Home";
import Login from "../pages/Login";
import Calendrier from '../pages/calendrier'; // Assure-toi que la majuscule est correcte !

export default function AppRouter() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Login />} />
        <Route path="/login" element={<Login />} />
        <Route path="/home" element={<Home />} />
        <Route path="/calendrier" element={<Calendrier />} /> {/* ✅ correctement placé ici */}
      </Routes>
    </BrowserRouter>
  );
}
