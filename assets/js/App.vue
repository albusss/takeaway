<template>
    <div>
        <MessageTable :type="'error'"></MessageTable>
        <MessageTable :type="'delivered'"></MessageTable>
    </div>
</template>
<script>
    import MessageTable from './MessageTable';

    export default {
        components: {MessageTable},
    }
</script>