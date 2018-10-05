// using vuejs to display a list of customers on the search customer page
const url = "/api/search_customers/";

let app = new Vue({
  el: ".grid",
  data: {
    search: "",
    tableStyle: {
      display: "none"
    },
    customers: null,
    custRef: ""
  },
  // check that if the values are deleted from the search box, clear the previous reference
  watch: {
    search: function(change) {
      if (this.search == "") {
        this.custRef = "";
      }
    }
  },
  methods: {
    // relocate to the customer's page
    loadCust(id) {
      window.location.href = `./customer/${id}`;
    },
    addRef(customer) {
      this.custRef = customer.customerID;
      this.customers = null;
      this.tableStyle.display = "none";
      this.search = `${customer.fname}  ${customer.sname}`;
    },
    // find the customers based on the input field, if no results clear the table
    lookup() {
      if (!this.search) {
        this.tableStyle.display = "none";
        this.customers = null;
        return;
      }

      fetch(url + this.search)
        .then(res => res.json())
        .then(data => {
          this.tableStyle.display = "block";
          this.customers = data.result;
        })
        .catch(err => console.log("error is", err));
    }
  }
});
