import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { apiFetch } from '../services/api';

export default function InviteValidation() {
  const { token } = useParams();
  const [statusMessage, setStatusMessage] = useState('Validation en cours...');

  useEffect(() => {
    const validateInvitation = async () => {
      try {
        const response = await apiFetch(`workspaces/invite/${token}`, { method: 'POST' });
        setStatusMessage(response.message || 'Invitation validée.');
      } catch (error) {
        setStatusMessage(error.message || 'Erreur lors de la validation.');
      }
    };

    validateInvitation();
  }, [token]);

  return (
    <div className="workspace-detail-page">
      <h2>{statusMessage}</h2>
    </div>
  );
}
