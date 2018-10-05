// uses vuejs as an ajax request to add platforms to the current page
let app = new Vue({
  el: ".grid",
  data: {
    checkedPlatforms: [],
    platforms: [],
    style: {
      display: "none"
    }
  },
  // once loaded call the getPlatforms method
  mounted() {
    this.getPlatforms();
  },
  methods: {
    getPlatforms() {
      // using the fetch api get all the platforms and add them to the model data
      fetch("/api/platforms")
        .then(res => res.json())
        .then(data => {
          this.platforms = data.result;
          this.style.display = "block";
        })
        .catch(err => console.log(`error is ${err}`));
    }
  }
});
