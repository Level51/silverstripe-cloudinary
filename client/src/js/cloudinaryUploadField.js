import Vue from 'vue';
import Level51CloudinaryUpload from 'src/App.vue';
import watchElement from './util';

const render = (el) => {
  new Vue({
    render(h) {
      return h(Level51CloudinaryUpload, {
        props: {
          payload: JSON.parse(el.dataset.payload)
        }
      });
    }
  }).$mount(`#${el.id}`);
};

watchElement('.level51-cloudinaryUpload', (el) => {
  setTimeout(() => {
    render(el);
  }, 1);
});
