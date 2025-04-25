const fetchMercureToken = async () => {
    const res = await fetch('http://localhost:8000/api/mercure-token', {
      headers: {
        Authorization: `Bearer ${userAuthToken}` 
      }
    });
  
    const data = await res.json();
    return data.token;
  };
  