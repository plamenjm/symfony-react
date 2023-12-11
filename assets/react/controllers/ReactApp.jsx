import React, { useEffect, useState } from 'react';

export default function ({fullName}) {
    const [stateResponse, setResponse] = useState({fullName})

    useEffect(() => {
        const baseUrl = window.location.origin + '/api/'
        const resource = baseUrl + 'params'
        const request = {
            method: 'GET',
            headers: {Accept: 'application/json'},
            //body: JSON.stringify({}),
            mode: 'no-cors',
            credentials: 'omit',
            cache: 'no-store',
        }
        fetch(resource, request)
          .then(response => response.json().then(json =>
            setResponse({fullName: JSON.stringify(json)}))
          )
          .catch(error => setResponse({fullName: error.message}))
    }, [])

    return <div>Hello {stateResponse.fullName}</div>;
}
