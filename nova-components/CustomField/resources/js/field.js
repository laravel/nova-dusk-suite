import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('index-custom-field', IndexField);
  app.component('detail-custom-field', DetailField);
  app.component('form-custom-field', FormField);
})
