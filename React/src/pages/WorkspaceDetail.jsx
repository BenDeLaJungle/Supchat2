import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { apiFetch } from '../services/api';
import AdminHeader from './Adminheader';

export default function WorkspaceDetail() {
  const { workspaceId } = useParams();
  const [channels, setChannels] = useState([]);
  const [workspaceName, setWorkspaceName] = useState('');

  useEffect(() => {
    const fetchChannels = async () => {
      const data = await apiFetch(`workspaces/${workspaceId}/channels`);
      setChannels(data);
    };
    fetchChannels();
  }, [workspaceId]);

  useEffect(() => {
    const fetchWorkspaceDetails = async () => {
      const data = await apiFetch(`workspaces/${workspaceId}`);
      setWorkspaceName(data.name);
    };
    fetchWorkspaceDetails();
  }, [workspaceId]);

  return (
    <>
      <AdminHeader />
      <div className="workspace-detail-page">
        <h2 className="workspace-detail-title">{workspaceName}</h2>

        <ul className="channel-list">
          {channels.map(channel => (
            <li key={channel.id} className="channel-list-item">
              <Link to={`/channels/${channel.id}`} className="channel-list-link">
                {channel.name}
              </Link>
            </li>
          ))}
        </ul>
      </div>
    </>
  );
}


