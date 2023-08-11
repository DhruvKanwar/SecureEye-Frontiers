  @extends('layouts.main')
  @section('title', 'Daily Report')
  @section('content')
  <div class="row layout-top-spacing" id="divbox">
      <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
          <div class="widget-content widget-content-area br-6">
              <div class="widget-content widget-content-area">
                  <form class="simple-example" v-if="!redirect_email">
                      <div class="form-row">
                          <div class="col-md-8 mb-4">
                              <label for="fullName">Segments</label>
                              <div class="form-group col-md-4">
                                  <select class="form-control tagging approvalReq" v-model="selectedSegment" id="segment" @change="get_locaion_segment();check_cctv(selectedSegment)">
                                      <option selected>--Select--</option>
                                      @foreach($segment_data as $segment)
                                      <option value="{{$segment->id}}">{{$segment->name}}</option>
                                      @endforeach

                                  </select>
                                  <br />

                                  <ul class="schools" style="list-style-type: none; -webkit-columns: 1;-moz-columns: 1;columns: 1;">
                                      <li v-for="(location,index) in locations" style="font-weight:bold">
                                          <input type="checkbox" class="locationCheckbox" v-model="module" :value="location.location_id" /> @{{index+1}} . @{{location.name}}
                                      </li>
                                  </ul>

                                  <div>
                                      <label>Location Selected</label>
                                      <span>@{{module}}</span>
                                  </div><br />
                                  <div v-if="cctv_flag">
                                      <label>Count of CCTV Working</label>
                                      <input type="number" v-model="cctv_working" />
                                  </div>
                              </div>

                          </div>
                      </div>
                      <!-- <button class="btn btn-primary" type="button" @click="send_email_it()">Send IT Email</button> -->
                      <button class="btn btn-primary" type="button" @click="submit_segment_report()">Submit form</button>
                  </form>
                  <a class="btn btn-primary" href="{{url('send_regional_email')}}" v-if="redirect_email">Redirect to Email</a>

              </div>
          </div>
      </div>
  </div>
  <script>
      new Vue({
          el: '#divbox',
          // components: {
          //   ValidationProvider
          // },
          data: {
              locations: [],
              module: [],
              cctv_working: "",
              selected_segment_id: "",
              cctv_flag: false,
              selectedSegment: "--Select--",
              redirect_email: false,


          },
          created: function() {
              // alert(this.got_details)
              //   alert('hello');
          },
          methods: {
              check_cctv: function(id) {
                  if (id == 1) {
                      this.cctv_flag = true;
                  } else {
                      this.cctv_flag = false;
                  }

              },
              send_email_it: function() {
                  axios.get('/send_email_to_it')
                      .then(response => {

                          if (response.data == 1) {
                              if (response.data == 1) {
                                  //   this.url = '/download_excel';
                                  //   window.location.href = this.url;
                              }
                              alert('Record Submitted Successfuly..')
                              swal('success', 'Record Submitted Successfuly..', 'success');
                          } else {
                              alert("Record Already Exists")
                              swal('error', 'Record Already Exists', 'error');

                          }
                      }).catch(error => {

                          console.log(error)


                      })

              },
              submit_segment_report: function() {
                  this.redirect_email = false;
                  if (this.selectedSegment == "--Select--") {
                      alert("Please Select the segment");
                      return 1;
                  }
                  const allChecks = document.querySelectorAll('.locationCheckbox');

                  var uncheckedCheckboxes = [];

                  allChecks.forEach(function(checkbox) {
                      if (!checkbox.checked) {
                          uncheckedCheckboxes.push(checkbox.value);
                      }
                  });

                  var unchecked_location_ids = uncheckedCheckboxes.join(',');



                  axios.post('/submit_segment_report', {
                          'selected_locatiion_id': this.module,
                          'cctv_working': this.cctv_working,
                          'segment_id': this.selected_segment_id,
                          'unchecked_location_ids': unchecked_location_ids

                      })
                      .then(response => {


                          if (response.data == 1) {
                              if (response.data == 1) {
                                  //   this.url = '/download_excel';
                                  //   window.location.href = this.url;
                              }
                              alert('Record Submitted Successfuly..')
                            //   window.location.reload();
                              swal('success', 'Record Submitted Successfuly..', 'success');
                          } else if (response.data.name) {
                              alert("These Locations " + response.data.name + " Entry has been already submitted for all the Segments")

                          } else if (response.data == "101") {
                              this.redirect_email = true;
                          }
                      }).catch(error => {

                          console.log(error)


                      })

              },
              get_locaion_segment: function() {
                  this.selected_segment_id = document.getElementById('segment').value;
                  this.locations = [];
                  this.module = [];
                  axios.post('/get_locaions', {
                          'segment_id': this.selected_segment_id,
                      })
                      .then(response => {
                          if (response.data) {
                              this.locations = response.data;
                              console.log(this.locations)
                          }
                      }).catch(error => {

                          console.log(error)


                      })

              },

              open_rejected_remarks_modal: function() {
                  this.rejected_remarks_modal = true;
              },

              open_file_view_modal: function(ter_id) {
                  // var file;
                  // file=document.getElementById('table_file_name').value;
                  this.id = ter_id;
                  this.file_view_modal = true;
                  axios.post('/get_file_name', {
                          'id': this.id,
                      })
                      .then(response => {
                          this.view_file_name = 'rejected_ter_uploads/' + response.data;
                          this.file_view_modal = true;
                      }).catch(error => {

                          swal('error', error, 'error')
                          this.file_view_modal = false;
                          this.ter_id = "";
                      })
              },
              update_payment: function(type) {
                  if (type == 'reject') {
                      this.ter_type = 'cancel';
                  } else if (type == 'accept') {
                      this.ter_type = 'accept';
                  }
                  this.total_amount = document.getElementById('total_amount').value;
                  if (this.ter_remarks != "" && this.file != null) {
                      if (parseInt(this.total_amount) >= this.payable_amount) {
                          const config = {
                              headers: {
                                  'content-type': 'multipart/form-data',
                              }
                          }
                          let formData = new FormData();
                          formData.append('file', this.file);
                          formData.append('ter_id', this.ter_id);
                          formData.append('remarks', this.ter_remarks);
                          formData.append('ter_type', this.ter_type);
                          // formData.append('payable_amount', this.payable_amount);


                          axios.post('/update_rejected_ter', formData, config)
                              .then(response => {
                                  if (response.data[0] === "duplicate_voucher") {
                                      swal('error', "Voucher Code : " + response.data[1] + " has been Already used", 'error')
                                  } else if (response.data) {
                                      swal('success', "Ter Id :" + this.ter_id + " has been sent to HR for payment", 'success')
                                      location.reload();
                                  } else {
                                      swal('error', "System Error", 'error')
                                      this.ter_modal = false;
                                      this.ter_id = "";
                                      location.reload();

                                  }

                              }).catch(error => {

                                  swal('error', error, 'error')
                                  this.ter_modal = false;
                                  this.ter_id = "";
                              })
                      } else {
                          swal('error', "Payable Amount = " + this.payable_amount + " can't be greater than Total Amount = " + this.total_amount, 'error')
                      }
                  } else {
                      swal('error', "Fields are Empty", 'error')
                  }
              },
              open_ter_modal: function(ter_id) {
                  this.ter_modal = true;
                  this.ter_id = ter_id;
                  // axios.post('/check_deduction', {
                  //         'ter_id': this.ter_id,
                  //     })
                  //     .then(response => {
                  //         if (response.data[0] == "success") {
                  //             this.diff_amount=response.data[1];
                  //             this.actual_amount=response.data[2];
                  //             this.prev_payable_sum=response.data[3];
                  //         } else {
                  //             swal('error', "All dues are paid", 'error')
                  //             this.partial_paid_modal=false;
                  //             $('#partialpaidModal').modal('hide');
                  //         }
                  //         this.partial_remarks="";
                  //         this.payable_amount="";
                  //         this.voucher_code="";
                  //      document.getElementById("fileupload").value="";

                  //     }).catch(error => {

                  //         swal('error', error , 'error')
                  //             this.ter_modal=false;
                  //             this.ter_id="";
                  //     })
                  // this.partial_paid_modal = true;
              },

              group_pay_now: function() {
                  var x = this.$el.querySelector("#tb");
                  var box = x.querySelectorAll(".selected_box");
                  var id = "";

                  for (var i = 0; i < box.length; i++) {
                      // console.log(y[i].value);
                      if (box[i].checked) {
                          if (id == "") {
                              id += box[i].value;
                          } else {
                              id += "|" + box[i].value;
                          }
                      }
                  }


                  const id_array = id.split("|");
                  if (id_array != "") {
                      axios.post('/group_pay_now', {
                              'selected_id': id
                          })
                          .then(response => {
                              console.log(response.data);
                              if (response.data >= 1) {
                                  // alert('hello')
                                  location.reload();
                              } else {
                                  swal('error', "Either Record is already updated,not selected or greater payable amount", 'error')
                              }

                          }).catch(error => {

                              console.log(response)
                              this.apply_offer_btn = 'Apply';

                          })
                  } else {
                      swal('error', "Either Record is not selected or field is empty", 'error')
                  }

              },


              pay_now_ter: function($id) {
                  var unique_id;
                  if ($id == "default") {
                      unique_id = this.rejected_id;
                  } else {
                      unique_id = $id;
                  }

                  this.ter_id = unique_id;
                  axios.post('/status_change_to_handover', {
                          'selected_id': unique_id
                      })
                      .then(response => {
                          console.log(response.data);
                          if (response.data == 1) {
                              swal('success', "Ter Id :" + this.ter_id + " has been approved", 'success')
                              location.reload();
                          } else if (response.data == 2) {
                              swal('success', "Ter Id :" + this.ter_id + " has been Cancelled", 'success')
                              location.reload();
                          } else {
                              swal('error', response.data[1], 'error')
                              this.ter_id = "";
                          }

                      }).catch(error => {

                          console.log(response)
                          this.apply_offer_btn = 'Apply';

                      })

              }
          }


      })
  </script>
  @endsection