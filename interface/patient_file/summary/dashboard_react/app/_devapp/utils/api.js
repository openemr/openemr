
const PatientApi = {
        fetch: (v1, v2) => {
            return fetch(`../../../../apis/api/patient/${v1}/${v2}`, {
                method: "GET",
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            })

    },
}

export default {
    PatientApi
};