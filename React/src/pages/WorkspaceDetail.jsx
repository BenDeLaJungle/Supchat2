import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { apiFetch } from '../services/api';
import AdminHeader from './Adminheader'; // Ajout du Header

export default function WorkspaceDetail() {
  const { workspaceId } = useParams();
  const [channels, setChannels] = useState([]);

  useEffect(() => {
    const fetchChannels = async () => {
      const data = await apiFetch(`workspaces/${workspaceId}/channels`);
      setChannels(data);
    };
    fetchChannels();
  }, [workspaceId]);

  return (
    <>
      <AdminHeader /> {/* Ici */}
      <div>
        <h2>Canaux du Workspace {workspaceId}</h2>
        <ul>
          {channels.map(channel => (
            <li key={channel.id}>
              <Link to={`/channels/${channel.id}`}>{channel.name}</Link>
            </li>
          ))}
        </ul>
      </div>
    </>
  );
}

