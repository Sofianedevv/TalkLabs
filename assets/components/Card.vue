<template>
  <div class="card">
    <div class="card-image">
      <img :src="imageUrl" :alt="title" />
    </div>
    <div class="card-content">
      <h3 class="card-title">{{ title }}</h3>
      <div class="card-author-container">
        <div class="card-author">{{ author }}</div>
        <div class="tag-container">
          <span v-for="(tag, index) in tags" :key="index" class="tag" :class="'tag-' + tag.color">
            {{ tag.text }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Card',
  props: {
    imageUrl: {
      type: String,
      required: true
    },
    title: {
      type: String,
      required: true
    },
    author: {
      type: String,
      required: true
    },
    tags: {
      type: Array,
      required: true
    }
  }
}
</script>

<style scoped>
.card {
  background-color: #1a1a24;
  border-radius: 10px;
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  position: relative;
  width: 100%;
  max-width: 350px;
  height: 300px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

.card-image {
  width: 100%;
  height: 236px;
  position: relative;
  overflow: hidden;
}

.card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.card-content {
  padding: 10px 15px;
  background-color: #23233F;
  height: 64px;
  width: 100%;
  box-sizing: border-box;
  overflow: hidden;
  margin-top: 0;
  border-radius: 0;
}

.card-title {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 5px;
  color: white;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.card-author-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0;
}

.card-author {
  font-size: 14px;
  color: #aaa;
}

.tag-container {
  display: flex;
  flex-wrap: nowrap;
  gap: 5px;
}

.tag {
  padding: 3px 6px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 500;
}

.tag-yellow {
  background-color: #FFD700;
  color: #333;
}

.tag-green {
  background-color: #23CE6B;
  color: white;
}

.tag-pink {
  background-color: #FF69B4;
  color: white;
}

.tag-blue {
  background-color: #1E90FF;
  color: white;
}
</style> 