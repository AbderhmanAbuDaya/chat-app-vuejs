<template>
    <div class="conversation">
       <h1>{{contact?contact.name:"Select a contact"}}</h1>
        <MessagesFeed :contact="contact" :messages="messages" ></MessagesFeed>
        <MessageComposer @send="sendMessage" @showTyping="onTyping" ></MessageComposer>
    </div>
</template>
<script>
import MessagesFeed from "./MessagesFeed";
import MessageComposer from "./MessageComposer";

export default {
    name:'Conversation',
     props:{
        contact:{
            type: Object,
            default:null
        },
         messages:{
            type: Array,
             default: []
         }
     },
    components:{
        MessagesFeed,
        MessageComposer

    },
    methods:{
        sendMessage(text){
          if (!this.contact){
              return ;
          }
          axios.post('/conversation/send/',{
              contact_id:this.contact.id,
              text:text
          }).then(res=>{
              this.$emit('new',res.data)
          })
        },
        onTyping(){
            if (this.contact) {
                console.log(`messages.${this.contact.id}`)
                Echo.private(`messages.${this.contact.id}`)
                    .whisper('typing', {
                        user: this.contact.id
                    });
               // console.log("ontyping")

                return ;

            }
        //    console.log("ontyping2")
            return;

        }
    }
}
</script>

<style lang="scss" scoped>
.conversation {
    flex: 5;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    h1 {
        font-size: 20px;
        padding: 10px;
        margin: 0;
        border-bottom: 1px dashed lightgray;
    }
}
</style>
