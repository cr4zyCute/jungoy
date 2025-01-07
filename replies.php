<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .post, .comment, .reply {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .comment {
            margin-left: 20px;
        }
        .reply {
            margin-left: 40px;
        }
        .form-group {
            margin-bottom: 10px;
        }
        button {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Post System</h1>
    <div id="posts"></div>
    <form id="newPostForm">
        <div class="form-group">
            <textarea id="postContent" rows="3" placeholder="Write a new post..."></textarea>
        </div>
        <button type="submit">Add Post</button>
    </form>
</body>
<script>
    document.addEventListener("DOMContentLoaded", () => {
    const postsContainer = document.getElementById("posts");
    const newPostForm = document.getElementById("newPostForm");
    const postContent = document.getElementById("postContent");

    const loadPosts = async () => {
        const response = await fetch("fetch_posts.php");
        const posts = await response.json();

        postsContainer.innerHTML = "";
        posts.forEach(post => {
            const postDiv = document.createElement("div");
            postDiv.className = "post";
            postDiv.innerHTML = `
                <p>${post.content}</p>
                <form onsubmit="addComment(event, ${post.id})">
                    <textarea rows="2" placeholder="Write a comment..."></textarea>
                    <button type="submit">Add Comment</button>
                </form>
                <div id="comments-${post.id}"></div>
            `;
            postsContainer.appendChild(postDiv);

            loadComments(post.id);
        });
    };

    const loadComments = async (postId) => {
        const response = await fetch(`fetch_comments.php?post_id=${postId}`);
        const comments = await response.json();

        const commentsContainer = document.getElementById(`comments-${postId}`);
        commentsContainer.innerHTML = "";

        comments.forEach(comment => {
            const commentDiv = document.createElement("div");
            commentDiv.className = comment.parent_id ? "reply" : "comment";
            commentDiv.innerHTML = `
                <p>${comment.content}</p>
                <form onsubmit="addReply(event, ${comment.id}, ${postId})">
                    <textarea rows="2" placeholder="Write a reply..."></textarea>
                    <button type="submit">Reply</button>
                </form>
            `;
            commentsContainer.appendChild(commentDiv);
        });
    };

    const addComment = async (event, postId) => {
        event.preventDefault();
        const content = event.target.querySelector("textarea").value;
        await fetch("add_comment.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ postId, content })
        });
        loadComments(postId);
    };

    const addReply = async (event, commentId, postId) => {
        event.preventDefault();
        const content = event.target.querySelector("textarea").value;
        await fetch("add_comment.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ postId, content, parentId: commentId })
        });
        loadComments(postId);
    };

    newPostForm.addEventListener("submit", async (event) => {
        event.preventDefault();
        await fetch("add_post.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ content: postContent.value })
        });
        postContent.value = "";
        loadPosts();
    });

    loadPosts();
});

</script>
</html>
