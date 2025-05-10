import React, { useEffect, useState } from 'react';
import { apiFetch } from '../services/api';
import { Link } from 'react-router-dom';
import AdminHeader from './Adminheader';

export default function WorkspaceList() {
    const [workspaces, setWorkspaces] = useState([]);
  
    useEffect(() => {
      const fetchWorkspaces = async () => {
        const data = await apiFetch('workspaces');
        setWorkspaces(data);
      };
      fetchWorkspaces();
    }, []);
  
    return (
      <>
        <AdminHeader /> {/* Ici */}
        <div>
          <h2>Liste des Workspaces</h2>
          <ul>
            {workspaces.map(ws => (
              <li key={ws.id}>
                <Link to={`/workspaces/${ws.id}`}>{ws.name}</Link>
              </li>
            ))}
          </ul>
        </div>
      </>
    );
  }
  