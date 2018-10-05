// using vuejs to search for movies, updating the page as the user types
const url = "/api/search_movie/";

let app = new Vue({
  el: ".grid",
  data: {
    search: "",
    cardStyle: {
      display: "none"
    },
    movies: null
  },
  // display the grid once vuejs is ready
  mounted() {
    this.cardStyle.display = "grid";
  },
  methods: {
    // find and display any results from the values entered into the input field
    pingApi() {
      if (!this.search) {
        this.movies = null;
        return;
      }

      fetch(url + this.search)
        .then(res => res.json())
        .then(data => {
          this.movies = data.result;
        })
        .catch(err => console.log("error is", err));
    }
  }
});
