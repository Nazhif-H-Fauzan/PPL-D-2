const apiFetch = (url, options = {}) => fetch(url, {
    ...options,
    headers: {
        Accept: 'application/json',
        Authorization: 'Bearer demo-ui-token',
        ...(options.headers || {}),
    },
});

export default apiFetch;
