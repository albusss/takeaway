import axios from 'axios';

export default {
    getSuccessMessages() {
        return axios.get('/api/v1/message?type=delivered&limit=50').then(response => {
            return response.data.messages;
        });
    },
    getErrorMessages() {
        const yesterday = new Date(Date.now() - 3600 * 24 * 1000),
            yesterday_string = yesterday.getFullYear()+'-'+yesterday.getMonth()+'-'+yesterday.getDay()+' '+yesterday.getHours()+':'+yesterday.getMinutes()+':'+yesterday.getSeconds();
        return axios.get('/api/v1/message?type=not_delivered&from='+yesterday_string).then(response => {
            return response.data.messages;
        });
    }
}