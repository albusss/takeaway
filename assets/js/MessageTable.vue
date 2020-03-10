<template>
    <div class="card mt-2">
        <div class="card-body">
            <h5 class="card-title" v-if="type == 'error'">Messages that returned any other status then delivered in the last 24 hours</h5>
            <h5 class="card-title" v-else>50 last success messages</h5>
            <table class="table" >
                <thead class="thead-light">
                <tr>
                    <th>Id</th>
                    <th>Created</th>
                    <th>Restaurant</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th v-show="type == 'error'">Error</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="message in messages" :class="{'table-warning': message.status == 'new', 'table-danger': message.status == 'error', 'table-success': message.status == 'success'}">
                    <td>{{ message.id }}</td>
                    <td>{{ message.created }}</td>
                    <td>{{ message.restaurant_title }}</td>
                    <td>{{ message.phone }}</td>
                    <td>{{ message.status }}</td>
                    <td v-show="type == 'error'">{{ message.error }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    import MessageAPI from './MessageAPI';

    export default {
        name: "MessageTable",
        data() {
            return {
                messages: [],
            }
        },
        props: ['type', 'apiMethod'],
        created() {
            const method = this.type == 'error' ? 'getErrorMessages' : 'getSuccessMessages';
            MessageAPI[method]().then(messages => {
                this.messages = messages;
            });
        }
    }
</script>

<style scoped>

</style>