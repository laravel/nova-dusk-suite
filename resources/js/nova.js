Nova.booting((app) => {
  Nova.request().interceptors.response.use(
    response => {
      console.dir({ response })

      return response
    },
    error => {
      console.dir({ error })

      return Promise.reject(error)
    }
  )
})
