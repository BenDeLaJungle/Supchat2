import React, { useEffect, useState } from 'react';
import { apiFetch } from '../services/api'; 
import '../styles/SharedFiles.css'; 
import AdminHeader from "../components/ui/Adminheader";

export default function SharedFiles() {
  const [files, setFiles] = useState([]);
  const [error, setError] = useState('');

  useEffect(() => {
    const fetchFiles = async () => {
      try {
        const [mine, shared] = await Promise.all([
          apiFetch('files/me'),
          apiFetch('files/shared')
        ]);

        setFiles([...mine, ...shared]);
      } catch (err) {
        setError("Erreur lors du chargement des fichiers.");
      }
    };

    fetchFiles();
  }, []);
  
	const handleDelete = async (fileId) => {
	  const confirmed = window.confirm("Voulez-vous vraiment supprimer ce fichier ?");
	  if (!confirmed) return;

	  try {
		await apiFetch(`files/${fileId}`, {
		  method: 'DELETE',
		});
		setFiles(prev => prev.filter(f => f.id !== fileId));
	  } catch (err) {
		alert("Erreur lors de la suppression.");
	  }
	};
  //  Fonction qui récupère l’URL sécurisée
  const handleDownload = async (fileId) => {
    try {
      const res = await apiFetch(`files/${fileId}/generate-download-url`);
      //const signedUrl = `http://localhost:8000${res.url}`;
      window.open(res.url, '_blank');
    } catch (e) {
      alert("Erreur lors de la génération du lien de téléchargement.");
    }
  };

  return (
    <>
      <AdminHeader />
      <div className="shared-files-container">
        <h2>Fichiers partagés</h2>

        {error && <p className="error">{error}</p>}
        {files.length === 0 && <p>Aucun fichier partagé.</p>}

        <ul className="file-list">
		  {files.map(file => (
			<li key={file.id} className="file-item">
			  <div className="file-header">
				<span className="file-link">
				  <a
					href={`http://localhost:8000${file.path}`}
					target="_blank"
					rel="noopener noreferrer"
				  >
					{file.name}
				  </a>
				</span>

				<div className="file-actions">
				  <button
					onClick={() => handleDownload(file.id)}
					className="download-icon"
					title={`Télécharger ${file.name}`}
				  >
					⬇️
				  </button>
				  <button
					onClick={() => handleDelete(file.id)}
					className="delete-icon"
					title="Supprimer"
				  >
					❌
				  </button>
				</div>
			  </div>

			  {file.author && (
				<div className="file-author">Envoyé par {file.author}</div>
			  )}
			</li>

		  ))}
		</ul>

      </div>
    </>
  );
}
