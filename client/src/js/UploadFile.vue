<template>
  <div class="level51-cu-uploadedFile">
    <img
      class="level51-cu-thumbnail"
      :src="file.thumbnailURL">
  </div>
</template>

<script>
/*
 * TODO
 *  - styling
 *  - remove action
 *  - meta info
 *  - ...
 */
export default {
  props: {
    file: {
      type: Object,
      required: true
    }
  }
};
</script>

<style lang="less">
  @import (reference) "~styles/vars";

  .level51-cu-uploadedFile {
    margin: @space-2;
  }
</style>
