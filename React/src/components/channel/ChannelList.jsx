import React, { useEffect, useState } from 'react';
import { apiFetch } from '../../services/api';

const ChannelList = ({ workspaceId, onSelectChannel }) => {
  const [channels, setChannels] = useState([]);

  useEffect(() => {
    const fetchChannels = async () => {
      const data = await apiFetch(`workspaces/${workspaceId}/channels`);
      setChannels(data);
    };
    fetchChannels();
  }, [workspaceId]);

  return (
    <div>
      <h2>Canaux</h2>
      <ul>
        {channels.map(channel => (
          <li key={channel.id}>
            <button onClick={() => onSelectChannel(channel.id)}>
              {channel.isPrivate ? '🔒' : '#'} {channel.name}
            </button>
          </li>
        ))}
      </ul>
    </div>
  );
};

export default ChannelList;
