module.exports = {
    methods: {
        objectLength(obj) {
            if (Object.keys(obj).length > 0) {
                return true;
            }
            return false;
        }
    }
}