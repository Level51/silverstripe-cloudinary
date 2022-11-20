import Vue from 'vue';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon, FontAwesomeLayers } from '@fortawesome/vue-fontawesome';

import {
  faTrashCan, faUpload, faExternalLink,
} from '@fortawesome/free-solid-svg-icons';

library.add(
  faTrashCan, faUpload, faExternalLink,
);

Vue.component('fa-icon', FontAwesomeIcon);
Vue.component('fa-layers', FontAwesomeLayers);
