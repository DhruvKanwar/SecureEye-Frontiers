  @extends('layouts.main')
  @section('title', 'Send Mail')
  @section('content')
  <div class="row layout-top-spacing" id="divbox">
      <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
          <div class="widget-content widget-content-area br-6">
              <div class="widget-content widget-content-area">
                  <form class="simple-example">
                      <div class="form-row">
                          <div class="col-md-8 mb-4">
                              <label for="fullName">Locations</label>
                              <div class="form-group col-md-4">
                                  @if (isset($data['id']) && isset($data['name']) && count($data['id']) === count($data['name']))
                                  <select class="form-control tagging approvalReq" id="location">
                                      @foreach ($data['id'] as $index => $id)
                                      @php $name = $data['name'][$index]; @endphp
                                      <option value="{{ $id }}">{{ $name }}</option>
                                      @endforeach
                                  </select>
                                  @endif
                                  <br />
                              </div>

                          </div>
                      </div>
                      @if (isset($data['id']) && isset($data['name']) && count($data['id']) === count($data['name']))
                      <button class="btn btn-primary" type="button" @click="mail_locationwise_report()">Send Email</button>
                      @else
                      <button class="btn btn-primary" type="button" @click="send_email_it()">Send IT Email</button>
                      @endif
                  </form>
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
              location_id: "",

          },
          created: function() {
              // alert(this.got_details)
              //   alert('hello');
          },
          methods: {
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
              mail_locationwise_report: function() {
                  this.location_id = document.getElementById('location').value;

                  axios.post('/mail_locationwise_report', {
                          'location_id': this.location_id

                      })
                      .then(response => {


                          if (response.data == 1) {
                              if (response.data == 1) {
                                  //   this.url = '/download_excel';
                                  window.location.reload();
                                  //   window.location.href = this.url;
                              }
                              alert('Record Submitted Successfuly..')
                              swal('success', 'Record Submitted Successfuly..', 'success');
                          } else if (response.data.name) {
                              alert("These Locations " + response.data.name + " Entry has been already submitted for all the Segments")

                          }
                      }).catch(error => {

                          console.log(error)


                      })

              },


          }


      })
  </script>
  @endsection