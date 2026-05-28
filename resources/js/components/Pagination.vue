<template>
  <div class="flex flex-col md:flex-row items-center justify-between gap-4 mt-6">
    <div class="flex items-center gap-2">
      <span class="text-sm text-brand-desc">Rows per page:</span>
      <select 
        v-model="perPageValue" 
        @change="changePerPage"
        class="bg-brand-input border border-brand-border text-brand-text text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2"
      >
        <option :value="20">20</option>
        <option :value="50">50</option>
        <option :value="100">100</option>
      </select>
    </div>
    
    <div class="flex items-center gap-2">
      <span class="text-sm text-brand-desc mr-4">
        Showing {{ from }} to {{ to }} of {{ total }} entries
      </span>
      <button 
        @click="prevPage" 
        :disabled="currentPage === 1"
        :class="['px-3 py-1.5 rounded-lg border text-sm font-medium transition', 
                 currentPage === 1 ? 'border-brand-border text-brand-desc/50 cursor-not-allowed' : 'border-brand-border text-brand-text hover:bg-brand-input']"
      >
        Previous
      </button>
      
      <!-- Page numbers can go here if needed, keeping it simple for now -->
      <div class="flex items-center gap-1">
        <button 
          v-for="page in pages" 
          :key="page"
          @click="goToPage(page)"
          :class="['w-8 h-8 rounded-lg flex items-center justify-center text-sm font-medium transition',
                   page === currentPage ? 'bg-indigo-600 text-white' : 'text-brand-text hover:bg-brand-input border border-transparent hover:border-brand-border']"
        >
          {{ page }}
        </button>
      </div>

      <button 
        @click="nextPage" 
        :disabled="currentPage === lastPage"
        :class="['px-3 py-1.5 rounded-lg border text-sm font-medium transition', 
                 currentPage === lastPage ? 'border-brand-border text-brand-desc/50 cursor-not-allowed' : 'border-brand-border text-brand-text hover:bg-brand-input']"
      >
        Next
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Pagination',
  props: {
    pagination: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      perPageValue: 20
    }
  },
  watch: {
    'pagination.per_page': {
      immediate: true,
      handler(newVal) {
        if (newVal) {
          this.perPageValue = Number(newVal);
        }
      }
    }
  },
  computed: {
    currentPage() {
      return this.pagination.current_page || 1;
    },
    lastPage() {
      return this.pagination.last_page || 1;
    },
    total() {
      return this.pagination.total || 0;
    },
    from() {
      return this.pagination.from || 0;
    },
    to() {
      return this.pagination.to || 0;
    },
    pages() {
      const range = [];
      for (let i = Math.max(1, this.currentPage - 2); i <= Math.min(this.lastPage, this.currentPage + 2); i++) {
        range.push(i);
      }
      return range;
    }
  },
  methods: {
    changePerPage() {
      this.$emit('per-page-change', this.perPageValue);
    },
    prevPage() {
      if (this.currentPage > 1) {
        this.$emit('page-change', this.currentPage - 1);
      }
    },
    nextPage() {
      if (this.currentPage < this.lastPage) {
        this.$emit('page-change', this.currentPage + 1);
      }
    },
    goToPage(page) {
      if (page !== this.currentPage) {
        this.$emit('page-change', page);
      }
    }
  }
}
</script>
