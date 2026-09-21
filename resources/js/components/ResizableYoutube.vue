<template>
  <node-view-wrapper class="resizable-youtube"
    :style="{ display: 'inline-block', width: wrapperWidth, aspectRatio: '16/9', maxWidth: '100%', verticalAlign: 'top', margin: '0.5rem 0' }">
    <div class="youtube-resize-container" :class="{ 'ProseMirror-selectednode': selected }" :style="{
      width: '100%',
      height: '100%',
      display: 'inline-block',
      resize: 'horizontal',
      overflow: 'hidden',
      position: 'relative',
      border: selected ? '2px solid #68CEF8' : '2px solid transparent'
    }" @mouseup="onResizeEnd">
      <iframe :src="node.attrs.src" :style="{
        width: '100%',
        height: '100%',
        border: 'none',
        pointerEvents: selected ? 'none' : 'auto'
      }" allowfullscreen>
      </iframe>
    </div>
  </node-view-wrapper>
</template>

<script setup>
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-3'
import { computed } from 'vue'

const props = defineProps(nodeViewProps)

const wrapperWidth = computed(() => {
  const w = props.node.attrs.width
  return typeof w === 'number' ? `${w}px` : (w || '60%')
})

const onResizeEnd = (e) => {
  const el = e.currentTarget
  const width = el.offsetWidth

  // Only update if the width is significantly different (prevents saving percentages as pixels immediately)
  // If it's a percentage, parseInt gets the number. We check if it changed.
  const currentWidthNum = typeof props.node.attrs.width === 'number' ? props.node.attrs.width : el.parentElement.offsetWidth

  if (Math.abs(width - currentWidthNum) > 5) {
    props.updateAttributes({
      width: width,
      height: 'auto'
    })
  }
}
</script>
