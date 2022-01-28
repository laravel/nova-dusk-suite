export default {
  data: () => ({ isWorking: false, fileUploadsCount: 0 }),

  methods: {
    /**
     * Handle file upload finishing
     */
    handleFileUploadFinished() {
      this.fileUploadsCount--

      if (this.fileUploadsCount < 1) {
        this.fileUploadsCount = 0
        this.isWorking = false
      }
    },

    /**
     * Handle file upload starting
     */
    handleFileUploadStarted() {
      this.isWorking = true
      this.fileUploadsCount++
    },
  },
}
