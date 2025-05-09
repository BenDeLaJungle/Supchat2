import React from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import AdminHeader from "./Adminheader";
import '../styles/user.css';

const UserPage = () => {
    const location = useLocation();
    const navigate = useNavigate();
    const user = location.state?.user;

    if (!user) {
        return (
            <>
                <AdminHeader />
                <div className="container mx-auto p-4">
                    <p className='text-red-500 text-xl'>
                        Erreur : utilisateur introuvable
                    </p>
                    <button 
                        className='mt-4 bg-blue-500 text-white px-4 py-2 rounded'
                        onClick={() => navigate(-1)}
                    >
                        Retour
                    </button>
                </div>
            </>
        );
    }

    return (
        <>
            <AdminHeader />
            <div className="user">
                <h2 className='welcome-name'>Informations de l'Utilisateur</h2>
                <div className='user-info-card'>
                    <div className='user-info-item'>
                        <strong>Nom d'utilisateur :</strong> {user.username}
                    </div>
                    <div className='user-info-item'>
                        <strong>Email :</strong> {user.email}
                    </div>
                    <div className='user-info-item'>
                        <strong>Rôle :</strong> {user.role}
                    </div>
                    <div className='user-info-item'>
                        <strong>Thème :</strong> {user.theme ? 'Sombre' : 'Clair'}
                    </div>
                    <div className='user-info-item'>
                        <strong>Statut :</strong> {user.status}
                    </div>
                    <div className='user-info-item'>
                        <strong>Prénom :</strong> {user.firstName}
                    </div>
                    <div className='user-info-item'>
                        <strong>Nom :</strong> {user.lastName}
                    </div>
                </div>

                <button 
                    className="start-conv-btn"
                    onClick={() => navigate('/messaging', { state: { recipient: user.username } })}
                >
                    ✉️ Envoyer un message
                </button>
            </div>
        </>
    );
};

export default UserPage;
