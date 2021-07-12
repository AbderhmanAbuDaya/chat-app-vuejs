<template>
    <div class="chat-app">
     <Conversation :contact="selectedContact" :messages="messages" @new="saveNewMessage"></Conversation>
        <ContactList :contacts="contacts" @selected="startConversationWith"></ContactList>
    </div>
</template>

<script>
    import Conversation from "./Conversation";
    import ContactList from "./ContactList";

    export default {
         props:{
             user:{
                type:Object,
                 required:true,
             }
         },
        data(){
            return {
                selectedContact:null,
                messages:[],
                contacts:[],
            }
        },
        components:{
            Conversation,
            ContactList
        },
        created() {
            Echo.private(`messages.${this.user.id}`)
                .listen('NewMessage', (e) => {
                    console.log("chatApp")
                    console.log(`messages.${this.user.id}`)
                    console.log(e.message)
                    this.hanleIncoming(e.message);
                })  .listenForWhisper('typing', (e) => {
                console.log("ww")
                console.log(e.user);
            });
            console.log(this.user)
       axios.get('/contacts')
            .then(res=>{
                this.contacts=res.data;
                console.log(res.data);
            });
        },
        methods:{
            startConversationWith(contact){
                this.updateUnreadCount(contact,true);
                axios.get(`/conversation/${contact.id}`)
                .then(res=>{
                    this.messages=res.data;
                    this.selectedContact=contact
                });
            },
            saveNewMessage(res){
                this.messages.push(res)

            },
            hanleIncoming(message){
                if(this.selectedContact && message.from==this.selectedContact.id){
                    this.saveNewMessage(message);
                    return ;
                }
                console.log("ww")
                console.log(message)
                this.updateUnreadCount(message.from_contact,false);
            },
            updateUnreadCount(contact,rest){
                this.contacts=this.contacts.map((single)=>{
                    if (contact.id!=single.id){
                        return single;
                    }
                    if (rest)
                        single.unread=0;
                    else
                        single.unread+=1;
                    return single;
                })
            }
        }
    }
</script>


<style lang="scss" scoped>
.chat-app {
    display: flex;
}
</style>
