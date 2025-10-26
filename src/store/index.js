import { createStore } from 'vuex';
import auth from './auth';
import bible from './bible';
import dashboard from './dashboard';
import cashapp from './cashapp';
import points from './points';

export default createStore({
    modules: {
        auth,
        bible,
        dashboard,
        cashapp,
        points
    }
});